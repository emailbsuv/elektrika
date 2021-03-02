<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\FileAttachment;
use App\Models\MyObject;
use App\Models\Order;
use App\Models\PhotoAttachment;
use File;
use Illuminate\Http\Request;
use Storage;

class OrdersController extends Controller
{

    /**
     * Get a listing of all orders.
     *
     * @OA\Get(
     *     path="/orders",
     *     description="Get all orders.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ { 'id': 1, 'title': 'My new order', 'updated_at': 1111123232 }]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {

        $orders = Order::with('fileAttachments', 'photoAttachments')->get();

        return BaseResource::collection($orders);
    }

    /**
     * Get a listing of user orders.
     *
     * @OA\Get(
     *     path="/orders/my",
     *     description="Get user orders.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="[ { 'id': 1, 'title': 'My new order', 'updated_at': 1111123232 }]"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function indexMy()
    {
        $user = auth()->user();
        if ($user) {
            $myOrders = Order::where('user_id', $user->id)->with('fileAttachments', 'photoAttachments', 'proposalsCount')->get();

            return BaseResource::collection($myOrders);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Creating a new object.
     *
     * @OA\Post(
     *     path="/orders/create",
     *     description="Create a order.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     * @OA\RequestBody(
     *     request="CreateObject",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property( property="title", type="string", example="My new order" ),
     *                  @OA\Property( property="description", type="string", example="Электромонтаж на балконе и кухне" ),
     *                  @OA\Property( property="work_start", type="integer", example=2222222222 ),
     *                  @OA\Property( property="work_end", type="integer", example=111111111 ),
     *                  @OA\Property( property="address", type="string", example="Московская область, деревня Сколково" ),
     *                  @OA\Property( property="calls", type="boolean", example=true ),
     *                  @OA\Property( property="proposed_amount", type="number", example="100000" ),
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @return BaseResource
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $title = str_replace('"', '', $request->input('title'));
            if($title == null) {
                return response()->json(['message' => 'title field not found'], 422);
            }
            $description = str_replace('"', '', $request->input('description'));
            if($description == null) {
                return response()->json(['message' => 'description field not found'], 422);
            }
            $address = str_replace('"', '', $request->input('address'));
            if($address == null) {
                return response()->json(['message' => 'address field not found'], 422);
            }

            $myOrder = new Order([
                'user_id' => $user->id,
                'title' => $title,
                'description' => $description,
                'address' => $address,
            ]);
            $myOrder->save();

            return new BaseResource($myOrder);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Add a new Photo Attachment in order.
     *
     * @OA\Post(
     *     path="/orders/{id}/add-photo",
     *     description="Add a new Photo Attachment in order.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *      @OA\RequestBody(
     *      request="AddPhotoAttachment",
     *      required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="file", type="file", collectionFormat="multi", @OA\Items(type="string", format="xml")),
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function addFileAttachment(Request $request, $id)
    {
        if(!$request->hasFile('file')) {
            return response()->json(['message' => 'Upload file data not found'], 422);
        }
        $user = auth()->user();
        if ($user) {

            $file = $request->file('file');
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }

            $myOrder = Order::where('id', $id)->where('user_id', $user->id)->first();

            if ($myOrder) {
                $path = my_orders_files_path($user->id);
                $fileName = uniqid($user->id).'.'.File::extension($file->getClientOriginalName());
                $file->move($path, $fileName);

                $fileAttachment = new FileAttachment(['order_id' => $myOrder->id, 'file_path' => $fileName]);
                $fileAttachment->save();

                return response()->noContent();

            } else {
                return response()->json(['message' => 'Order not found.'], 401);
            }
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Add a new File Attachment in order.
     *
     * @OA\Post(
     *     path="/orders/{id}/add-file",
     *     description="Add a new File Attachment in order.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     * @OA\RequestBody(
     *     request="AddFileAttachment",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="file", type="file", collectionFormat="multi", @OA\Items(type="string", format="xml")),
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function addPhotoAttachment(Request $request, $id)
    {
        if(!$request->hasFile('file')) {
            return response()->json(['message' => 'Upload file data not found'], 422);
        }
        $user = auth()->user();
        if ($user) {

            $file = $request->file('file');
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }

            $myOrder = Order::where('id', $id)->where('user_id', $user->id)->first();

            if ($myOrder) {
                $path = my_orders_photos_path($user->id);
                $fileName = uniqid($user->id).'.'.File::extension($file->getClientOriginalName());
                $file->move($path, $fileName);

                $photoAttachment = new PhotoAttachment(['order_id' => $myOrder->id, 'file_path' => $fileName]);
                $photoAttachment->save();

                return response()->noContent();

            } else {
                return response()->json(['message' => 'Order not found.'], 401);
            }
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return void
     */
    public function load($id)
    {
        $user = auth()->user();
        if ($user) {

        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/orders/update/",
     *     description="Update user object.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     * @OA\RequestBody(
     *     request="UpdateObject",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                    property="id",
     *                    type="integer",
     *                    example="43243"
     *                  ),
     *                  @OA\Property(
     *                    property="title",
     *                    type="string",
     *                    example="My new house"
     *                  ),
     *                  @OA\Property(property="xml", type="file", collectionFormat="multi", @OA\Items(type="string", format="xml")),
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MyObject  $myObject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MyObject $myObject)
    {
        $user = auth()->user();
        if ($user) {
            $id = $request->input('id');
            if($id == null) {
                return response()->json(['message' => 'id field not found'], 422);
            }
            $myObject = MyObject::where('user_id', $user->id)->where('id', $id)->first();
            if (!$myObject) {
                return response()->json(['message' => 'Object not found.'], 401);
            }
            $filePath = my_objects_path($user->id) . $myObject->xml_path;
            $title = str_replace('"', '', $request->input('title'));
            if($title == null) {
                return response()->json(['message' => 'title field not found'], 422);
            }
            $file = $request->file('xml');
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }

            $file->move($filePath);

            $myObject->title = $title;
            $myObject->save();

            return response()->noContent();
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/orders/remove/{id}",
     *     description="Remove the specified resource from storage.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Orders"},
     *     operationId="deleteMyObjectById",
     *     @OA\Parameter(
     *         description="ID of myObject to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *         )
     *      ),
     * )
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if ($user) {
            $myObject = MyObject::where('user_id', $user->id)->where('id', $id)->first();
            if ($myObject) {
                $filePath = my_objects_path($user->id) . $myObject->xml_path;
                Storage::delete($filePath);
                $myObject->delete();
                return response()->noContent();
            } else {
                return response()->json(['message' => 'Object not found.'], 401);
            }
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }
}
