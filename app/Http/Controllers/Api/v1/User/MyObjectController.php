<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\MyObject;
use Illuminate\Http\Request;
use Storage;

class MyObjectController extends Controller
{
    /**
     * Get a listing of user objects.
     *
     * @OA\Get(
     *     path="/my-object",
     *     description="Get user objects.",
     *     security={{"bearerAuth":{}}},
     *     tags={"my-object"},
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
     *                  example="[ { 'id': 1, 'title': 'My new house', 'updated_at': 1111123232 }]"
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
        $user = auth()->user();
        if ($user) {
            $myObjects = MyObject::where('user_id', $user->id)->get();

            return BaseResource::collection($myObjects);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Creating a new object.
     *
     * @OA\Post(
     *     path="/my-object/create",
     *     description="Create a new object.",
     *     security={{"bearerAuth":{}}},
     *     tags={"my-object"},
     * @OA\RequestBody(
     *     request="CreateObject",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
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
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(!$request->hasFile('xml')) {
            return response()->json(['message' => 'Upload xml data not found'], 422);
        }
        $user = auth()->user();
        if ($user) {
            $title = str_replace('"', '', $request->input('title'));
            if($title == null) {
                return response()->json(['message' => 'title field not found'], 422);
            }
            $file = $request->file('xml');
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }

            $path = my_objects_path($user->id);
            $fileName = uniqid($user->id).'.xml';
            $file->move($path, $fileName);

            $myObject = new MyObject(['user_id' => $user->id, 'title' => $title, 'xml_path' => $fileName]);
            $myObject->save();

            return response()->noContent();
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
     * Display the specified resource xml.
     *
     * @OA\Get(
     *     path="/my-object/{id}/xml",
     *     description="Get user objects xml file.",
     *     security={{"bearerAuth":{}}},
     *     tags={"my-object"},
     *
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(
     *                  @OA\Property(
     *                  property="data",
     *                  type="string",
     *                  example="xml"
     *                )
     *             )
     *         )
     *      ),
     * )
     *
     * @param $id
     * @return void
     */
    public function loadXml($id)
    {
        $user = auth()->user();
        if ($user) {
            $myObject = MyObject::where('user_id', $user->id)->where('id', $id)->first();
            if ($myObject) {
                $filePath = my_objects_path($user->id) . $myObject->xml_path;
                return response()->download($filePath);
            } else {
                return response()->json(['message' => 'Object not found.'], 401);
            }
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/my-object/update/",
     *     description="Update user object.",
     *     security={{"bearerAuth":{}}},
     *     tags={"my-object"},
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
     *     path="/my-object/remove/{id}",
     *     description="Get user objects xml file.",
     *     security={{"bearerAuth":{}}},
     *     tags={"my-object"},
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
