/**
 * Chunk Upload Handler
 * Xử lý upload file lớn bằng cách chia thành các chunks
 */
class ChunkUploadHandler {
    constructor(options = {}) {
        this.chunkSize = options.chunkSize || 1024 * 1024; // 1MB per chunk
        this.uploadUrl = options.uploadUrl || "/customer/design/upload-chunk";
        this.statusUrl = options.statusUrl || "/customer/design/upload-status";
        this.cancelUrl = options.cancelUrl || "/customer/design/upload-cancel";
        this.maxFileSize = options.maxFileSize || 100 * 1024 * 1024; // 100MB
        this.onProgress = options.onProgress || (() => {});
        this.onComplete = options.onComplete || (() => {});
        this.onError = options.onError || (() => {});
        this.onCancel = options.onCancel || (() => {});
    }

    /**
     * Upload file với chunk
     */
    async uploadFile(file) {
        try {
            // Kiểm tra kích thước file
            if (file.size > this.maxFileSize) {
                throw new Error(
                    `File quá lớn. Kích thước tối đa là ${this.formatFileSize(
                        this.maxFileSize
                    )}`
                );
            }

            // Tạo upload ID
            const uploadId = this.generateUploadId();

            // Tính số chunks
            const chunks = Math.ceil(file.size / this.chunkSize);

            console.log(
                `Bắt đầu upload file: ${file.name} (${this.formatFileSize(
                    file.size
                )})`
            );
            console.log(
                `Chia thành ${chunks} chunks, mỗi chunk ${this.formatFileSize(
                    this.chunkSize
                )}`
            );

            // Upload từng chunk
            for (let chunkIndex = 0; chunkIndex < chunks; chunkIndex++) {
                const start = chunkIndex * this.chunkSize;
                const end = Math.min(start + this.chunkSize, file.size);
                const chunk = file.slice(start, end);

                // Tạo FormData cho chunk
                const formData = new FormData();
                formData.append("file", chunk, file.name);
                formData.append("chunk", chunkIndex);
                formData.append("chunks", chunks);
                formData.append("filename", file.name);
                formData.append("upload_id", uploadId);
                formData.append("total_size", file.size);

                // Upload chunk
                const response = await this.uploadChunk(
                    formData,
                    chunkIndex,
                    chunks
                );

                if (response.completed) {
                    console.log("Upload hoàn tất!");
                    this.onComplete({
                        file: file,
                        filePath: response.file_path,
                        uploadId: uploadId,
                    });
                    return response.file_path;
                }

                // Cập nhật progress
                const progress = ((chunkIndex + 1) / chunks) * 100;
                this.onProgress({
                    file: file,
                    progress: progress,
                    chunkIndex: chunkIndex + 1,
                    totalChunks: chunks,
                    uploadId: uploadId,
                });

                // Delay nhỏ để tránh spam server
                await this.delay(100);
            }
        } catch (error) {
            console.error("Lỗi khi upload file:", error);
            this.onError({
                file: file,
                error: error.message,
            });
            throw error;
        }
    }

    /**
     * Upload một chunk
     */
    async uploadChunk(formData, chunkIndex, totalChunks) {
        try {
            console.log(
                `Uploading chunk ${chunkIndex + 1}/${totalChunks} to ${
                    this.uploadUrl
                }`
            );

            const response = await fetch(this.uploadUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: formData,
            });

            console.log(`Response status: ${response.status}`);
            console.log(`Response headers:`, response.headers);

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`HTTP Error ${response.status}: ${errorText}`);
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText} - ${errorText}`
                );
            }

            const result = await response.json();
            console.log(`Chunk upload result:`, result);

            if (!result.success) {
                throw new Error(result.error || "Upload chunk thất bại");
            }

            return result;
        } catch (error) {
            console.error(
                `Lỗi khi upload chunk ${chunkIndex + 1}/${totalChunks}:`,
                error
            );
            throw error;
        }
    }

    /**
     * Kiểm tra trạng thái upload
     */
    async checkUploadStatus(uploadId) {
        try {
            const response = await fetch(`${this.statusUrl}/${uploadId}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(
                    result.error || "Không thể kiểm tra trạng thái upload"
                );
            }

            return result;
        } catch (error) {
            console.error("Lỗi khi kiểm tra trạng thái upload:", error);
            throw error;
        }
    }

    /**
     * Hủy upload
     */
    async cancelUpload(uploadId) {
        try {
            const formData = new FormData();
            formData.append("upload_id", uploadId);

            const response = await fetch(this.cancelUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                this.onCancel({ uploadId: uploadId });
            }

            return result;
        } catch (error) {
            console.error("Lỗi khi hủy upload:", error);
            throw error;
        }
    }

    /**
     * Tạo upload ID
     */
    generateUploadId() {
        return (
            "upload_" +
            Date.now() +
            "_" +
            Math.random().toString(36).substr(2, 9)
        );
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }

    /**
     * Delay function
     */
    delay(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }
}

/**
 * File Upload Manager
 * Quản lý việc upload nhiều file
 */
class FileUploadManager {
    constructor(options = {}) {
        this.chunkUploader = new ChunkUploadHandler(options);
        this.uploadQueue = [];
        this.isUploading = false;
        this.onFileProgress = options.onFileProgress || (() => {});
        this.onFileComplete = options.onFileComplete || (() => {});
        this.onFileError = options.onFileError || (() => {});
        this.onAllComplete = options.onAllComplete || (() => {});
    }

    /**
     * Thêm file vào queue
     */
    addFile(file) {
        this.uploadQueue.push({
            file: file,
            status: "pending",
            progress: 0,
            error: null,
        });
    }

    /**
     * Thêm nhiều file
     */
    addFiles(files) {
        Array.from(files).forEach((file) => this.addFile(file));
    }

    /**
     * Bắt đầu upload tất cả file trong queue
     */
    async startUpload() {
        if (this.isUploading) {
            throw new Error("Đang upload, vui lòng chờ...");
        }

        this.isUploading = true;

        try {
            for (let i = 0; i < this.uploadQueue.length; i++) {
                const queueItem = this.uploadQueue[i];

                if (queueItem.status === "pending") {
                    queueItem.status = "uploading";

                    try {
                        const filePath = await this.chunkUploader.uploadFile(
                            queueItem.file
                        );

                        queueItem.status = "completed";
                        queueItem.progress = 100;
                        queueItem.filePath = filePath;

                        this.onFileComplete({
                            file: queueItem.file,
                            filePath: filePath,
                            index: i,
                        });
                    } catch (error) {
                        queueItem.status = "error";
                        queueItem.error = error.message;

                        this.onFileError({
                            file: queueItem.file,
                            error: error.message,
                            index: i,
                        });
                    }
                }
            }

            this.onAllComplete({
                completed: this.uploadQueue.filter(
                    (item) => item.status === "completed"
                ),
                errors: this.uploadQueue.filter(
                    (item) => item.status === "error"
                ),
            });
        } finally {
            this.isUploading = false;
        }
    }

    /**
     * Lấy trạng thái upload
     */
    getUploadStatus() {
        const total = this.uploadQueue.length;
        const completed = this.uploadQueue.filter(
            (item) => item.status === "completed"
        ).length;
        const errors = this.uploadQueue.filter(
            (item) => item.status === "error"
        ).length;
        const uploading = this.uploadQueue.filter(
            (item) => item.status === "uploading"
        ).length;

        return {
            total,
            completed,
            errors,
            uploading,
            pending: total - completed - errors - uploading,
            isUploading: this.isUploading,
        };
    }

    /**
     * Xóa file khỏi queue
     */
    removeFile(index) {
        if (index >= 0 && index < this.uploadQueue.length) {
            this.uploadQueue.splice(index, 1);
        }
    }

    /**
     * Xóa tất cả file
     */
    clearQueue() {
        this.uploadQueue = [];
    }

    /**
     * Lấy danh sách file đã upload thành công
     */
    getCompletedFiles() {
        return this.uploadQueue
            .filter((item) => item.status === "completed")
            .map((item) => item.filePath);
    }
}

// Export cho sử dụng global
window.ChunkUploadHandler = ChunkUploadHandler;
window.FileUploadManager = FileUploadManager;
