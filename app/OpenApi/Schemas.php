<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(property="field", type="array", @OA\Items(type="string", example="Error message"))
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD123456"),
 *     @OA\Property(property="status", type="string", example="on hold"),
 *     @OA\Property(property="store_name", type="string", example="My Store"),
 *     @OA\Property(property="channel", type="string", example="api"),
 *     @OA\Property(property="customer_email", type="string", example="john@example.com"),
 *     @OA\Property(
 *         property="shipping_address",
 *         type="object",
 *         @OA\Property(property="customer_name", type="string", example="John Doe"),
 *         @OA\Property(property="company", type="string", example="My Store"),
 *         @OA\Property(property="address_1", type="string", example="123 Main Street"),
 *         @OA\Property(property="address_2", type="string", example="Apt 4B"),
 *         @OA\Property(property="city", type="string", example="London"),
 *         @OA\Property(property="county", type="string", example="England"),
 *         @OA\Property(property="postcode", type="string", example="SW1A 1AA"),
 *         @OA\Property(property="country", type="string", example="GB"),
 *         @OA\Property(property="phone", type="string", example="+84123456789")
 *     ),
 *     @OA\Property(
 *         property="products",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderProduct")
 *     ),
 *     @OA\Property(property="label_url", type="string", example="http://example.com/label.pdf"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-20T10:00:00Z"),
 *     @OA\Property(property="total_price", type="string", example="20.00"),
 *     @OA\Property(
 *         property="transaction",
 *         ref="#/components/schemas/Transaction"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="OrderProduct",
 *     type="object",
 *     @OA\Property(property="part_number", type="string", example="TSHIRT-001"),
 *     @OA\Property(property="title", type="string", example="Summer Collection"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="print_price", type="string", example="10.00"),
 *     @OA\Property(property="total_price", type="string", example="20.00"),
 *     @OA\Property(
 *         property="designs",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Design")
 *     ),
 *     @OA\Property(
 *         property="mockups",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Mockup")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Design",
 *     type="object",
 *     @OA\Property(property="file_url", type="string", format="uri", example="https://example.com/design1.png"),
 *     @OA\Property(property="print_space", type="string", example="front")
 * )
 * 
 * @OA\Schema(
 *     schema="Mockup",
 *     type="object",
 *     @OA\Property(property="file_url", type="string", format="uri", example="https://example.com/mockup1.png"),
 *     @OA\Property(property="print_space", type="string", example="front")
 * )
 * 
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=456),
 *     @OA\Property(property="amount", type="string", example="20.00"),
 *     @OA\Property(property="status", type="string", example="approved"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-20T10:00:00Z")
 * )
 */
class Schemas {}
