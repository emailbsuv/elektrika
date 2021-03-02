<?php


namespace App\Http\Controllers\Api\v1\User;


use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * Get a listing of proposals by order.
     *
     * @OA\Get(
     *     path="/proposals/{id}",
     *     description="Get proposals.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposals"},
     *     @OA\Parameter(
     *         description="ID of order to return",
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
     * @param $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($id) {
        if(!$id) {
            return response()->json(['message' => 'Invalid id field'], 422);
        }
        $user = auth()->user();
        if ($user) {
            $proposals = Proposal::where('order_id', $id)->get();
            return BaseResource::collection($proposals);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }

    /**
     * Creating a new proposal.
     *
     * @OA\Post(
     *     path="/proposals/{id}/create",
     *     description="Create a proposal.",
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposals"},
     *     @OA\Parameter(
     *         description="ID of order to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     * @OA\RequestBody(
     *     request="CreateProposal",
     *     required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property( property="text", type="string", example="My new proposal" ),
     *                  @OA\Property( property="from", type="number", example=10000 ),
     *                  @OA\Property( property="to", type="number", example=20000 ),
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
     * @param $id
     * @param Request $request
     * @return BaseResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create($id, Request $request) {
        $rules = [
            'text' => 'required|string',
            'from' => 'nullable|numeric',
            'to' => 'nullable|numeric'
        ];

        $this->validate($request, $rules);

        $user = auth()->user();
        if ($user) {

            $proposal = new Proposal([
                'order_id' => $id,
                'user_id' => $user->id,
                'text' => $request->input('text'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ]);
            $proposal->save();

            return new BaseResource($proposal);
        } else {
            return response()->json(['message' => 'User not found.'], 401);
        }
    }
}
