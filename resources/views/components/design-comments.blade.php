@props(['taskId', 'currentUser'])

<div class="bg-white rounded-lg shadow-md border border-gray-200"
    data-task-id="{{ $taskId }}"
    data-user-id="{{ $currentUser->id }}"
    data-is-designer="{{ $currentUser->role === 'design' ? 'true' : 'false' }}">
    <!-- Comments Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-comments mr-2 text-blue-600"></i>
                Bình luận
            </div>
            <span class="text-sm text-gray-500 font-normal">Trao đổi với designer/khách hàng</span>
        </h3>
    </div>

    <!-- Comments List -->
    <div class="p-6">
        <div id="comments-container" class="space-y-4 max-h-96 overflow-y-auto">
            <!-- Comments sẽ được load bằng JavaScript -->
        </div>

        <!-- Loading -->
        <div id="comments-loading" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-500 mt-2">Đang tải bình luận...</p>
            <p class="text-xs text-gray-400 mt-1">Vui lòng chờ trong giây lát</p>
        </div>

        <!-- Empty State -->
        <div id="comments-empty" class="text-center py-8 hidden">
            <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 mb-2">Chưa có bình luận nào</p>
            <p class="text-sm text-gray-400">Hãy là người đầu tiên bình luận!</p>
        </div>
    </div>

    <!-- Add Comment Form -->
    <div class="px-6 py-4 border-t border-gray-200">
        <form id="comment-form" class="space-y-3">
            <div>
                <textarea
                    id="comment-content"
                    name="content"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 resize-none"
                    placeholder="Nhập bình luận của bạn... (Tối đa 1000 ký tự)"
                    maxlength="1000"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">
                        <span id="char-count">0</span>/1000 ký tự
                    </span>
                    <span id="char-warning" class="text-xs text-red-500 hidden">Sắp đạt giới hạn!</span>
                </div>
            </div>
            <div class="flex justify-end">
                <button
                    type="submit"
                    id="submit-comment"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fas fa-paper-plane mr-2"></i>
                    Gửi bình luận
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy dữ liệu từ data attributes
        var commentContainer = document.querySelector('[data-task-id]');
        var taskId = commentContainer.getAttribute('data-task-id');
        var currentUserId = commentContainer.getAttribute('data-user-id');
        var isDesigner = commentContainer.getAttribute('data-is-designer') === 'true';

        console.log('Comment container found:', commentContainer);
        console.log('Task ID from data:', taskId);
        console.log('Current user ID:', currentUserId);
        console.log('Is designer:', isDesigner);

        var commentsContainer = document.getElementById('comments-container');
        var commentsLoading = document.getElementById('comments-loading');
        var commentsEmpty = document.getElementById('comments-empty');
        var commentForm = document.getElementById('comment-form');
        var commentContent = document.getElementById('comment-content');
        var submitComment = document.getElementById('submit-comment');
        var charCount = document.getElementById('char-count');
        var charWarning = document.getElementById('char-warning');

        // Character counter
        commentContent.addEventListener('input', function() {
            var length = this.value.length;
            charCount.textContent = length;

            // Hiển thị warning khi gần đạt giới hạn
            if (length >= 900) {
                charWarning.classList.remove('hidden');
            } else {
                charWarning.classList.add('hidden');
            }

            if (length > 0 && length <= 1000) {
                submitComment.disabled = false;
            } else {
                submitComment.disabled = true;
            }
        });

        // Load comments
        function loadComments() {
            // Xác định route dựa trên role của user
            var baseUrl = isDesigner ? '/designer/tasks/' : '/customer/design/tasks/';
            var url = baseUrl + taskId + '/comments';

            console.log('Loading comments from:', url);
            console.log('Task ID:', taskId);
            console.log('Is Designer:', isDesigner);

            fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(function(response) {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('Comments data:', data);
                    commentsLoading.classList.add('hidden');

                    if (data.success) {
                        if (data.comments.length === 0) {
                            commentsEmpty.classList.remove('hidden');
                        } else {
                            displayComments(data.comments);
                        }
                    } else {
                        console.error('Lỗi khi tải comments:', data.error || data.message);
                        showNotification('Không thể tải bình luận. Vui lòng thử lại.', 'error');
                    }
                })
                .catch(function(error) {
                    commentsLoading.classList.add('hidden');
                    console.error('Error loading comments:', error);
                    showNotification('Không thể kết nối đến server. Vui lòng thử lại.', 'error');
                });
        }

        // Display comments
        function displayComments(comments) {
            console.log('Displaying comments:', comments);
            commentsContainer.innerHTML = '';

            comments.forEach(function(comment) {
                var commentElement = createCommentElement(comment);
                commentsContainer.appendChild(commentElement);
            });

            // Scroll to bottom
            commentsContainer.scrollTop = commentsContainer.scrollHeight;
        }

        // Create comment element
        function createCommentElement(comment) {
            console.log('Creating comment element:', comment);

            var div = document.createElement('div');
            div.className = 'flex ' + (comment.is_own ? 'justify-end' : 'justify-start');

            var bgColor = comment.is_own ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800';
            var maxWidth = 'max-w-xs';

            // Xác định role display name và màu sắc
            var roleDisplayName = comment.type === 'customer' ? 'Khách hàng' : 'Designer';
            var roleColor = comment.type === 'customer' ? 'text-blue-600' : 'text-green-600';
            var avatarColor = comment.type === 'customer' ? 'bg-blue-500' : 'bg-green-500';

            // Chuyển đổi link trong nội dung
            var processedContent = convertLinksToClickable(comment.content);

            div.innerHTML =
                '<div class="' + maxWidth + ' ' + bgColor + ' rounded-lg px-4 py-3 shadow-sm">' +
                '<div class="flex items-start space-x-2">' +
                '<div class="flex-shrink-0">' +
                '<div class="w-8 h-8 rounded-full ' + avatarColor + ' flex items-center justify-center">' +
                '<i class="fas fa-user text-sm text-white"></i>' +
                '</div>' +
                '</div>' +
                '<div class="flex-1 min-w-0">' +
                '<div class="flex items-center space-x-2 mb-1">' +
                '<span class="text-sm font-medium">' + comment.user_name + '</span>' +
                '<span class="text-xs ' + (comment.is_own ? 'opacity-75' : roleColor) + '">' + roleDisplayName + '</span>' +
                '</div>' +
                '<p class="text-sm break-words">' + processedContent + '</p>' +
                '<p class="text-xs opacity-75 mt-1">' + comment.created_at + '</p>' +
                '</div>' +
                '</div>' +
                '</div>';

            return div;
        }

        // Convert links to clickable links
        function convertLinksToClickable(text) {
            // Regex để phát hiện URL
            var urlRegex = /(https?:\/\/[^\s]+)/g;

            return text.replace(urlRegex, function(url) {
                // Đảm bảo URL có protocol
                var fullUrl = url.startsWith('http') ? url : 'https://' + url;

                // Tạo link với target="_blank" để mở trong tab mới
                return '<a href="' + fullUrl + '" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-80 transition-opacity">' + url + '</a>';
            });
        }

        // Escape HTML
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Submit comment
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var content = commentContent.value.trim();
            if (!content) return;

            submitComment.disabled = true;
            submitComment.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...';

            // Xác định route dựa trên role của user
            var baseUrl = isDesigner ? '/designer/tasks/' : '/customer/design/tasks/';
            var url = baseUrl + taskId + '/comments';

            console.log('Submitting comment to:', url);
            console.log('Content:', content);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content: content
                    })
                })
                .then(function(response) {
                    console.log('Submit response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('Submit response data:', data);
                    if (data.success) {
                        // Add new comment to container
                        var commentElement = createCommentElement(data.comment);
                        commentsContainer.appendChild(commentElement);

                        // Clear form
                        commentContent.value = '';
                        charCount.textContent = '0';
                        charWarning.classList.add('hidden');
                        submitComment.disabled = true;

                        // Hide empty state if it was shown
                        commentsEmpty.classList.add('hidden');

                        // Scroll to bottom
                        commentsContainer.scrollTop = commentsContainer.scrollHeight;

                        // Show success notification
                        showNotification('Bình luận đã được gửi thành công!', 'success');
                    } else {
                        showNotification(data.error || 'Có lỗi xảy ra khi gửi bình luận.', 'error');
                    }
                })
                .catch(function(error) {
                    console.error('Error submitting comment:', error);
                    showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                })
                .finally(function() {
                    submitComment.disabled = false;
                    submitComment.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Gửi bình luận';
                });
        });

        // Show notification
        function showNotification(message, type) {
            var notification = document.createElement('div');
            var bgColor = type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            notification.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ' + bgColor;
            notification.innerHTML =
                '<div class="flex items-center justify-between">' +
                '<div class="flex items-center">' +
                '<i class="fas ' + icon + ' mr-2"></i>' +
                '<span>' + message + '</span>' +
                '</div>' +
                '<button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">' +
                '<i class="fas fa-times"></i>' +
                '</button>' +
                '</div>';

            document.body.appendChild(notification);

            setTimeout(function() {
                notification.classList.remove('translate-x-full');
            }, 100);

            setTimeout(function() {
                notification.classList.add('translate-x-full');
                setTimeout(function() {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        // Load comments on page load
        console.log('Starting to load comments...');
        console.log('Task ID:', taskId);
        console.log('Is Designer:', isDesigner);
        console.log('Current User ID:', currentUserId);

        // Test if we can find the container
        if (!commentContainer) {
            console.error('Comment container not found!');
            return;
        }

        if (!taskId) {
            console.error('Task ID is empty!');
            return;
        }

        loadComments();
    });
</script>