
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Resources\HairStylistRequestResource;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HairStylistRequestController extends Controller
{
    // Existing methods...

    public function store(CreateHairStylistRequest $request)
    {
        // Check if the user exists
        $user = User::find($request->user_id);
        if (!$user) {
            throw new ModelNotFoundException('User not found.');
        }

        // Create a new Request instance with the validated data
        $hairStylistRequest = new Request([
            'area' => $request->area,
            'menu' => $request->menu,
            'hair_concerns' => $request->hair_concerns,
            'user_id' => $request->user_id,
            'status' => 'pending', // default status
        ]);

        // Save the new Request instance to the database
        $hairStylistRequest->save();

        // Return a response with the newly created request
        return new HairStylistRequestResource($hairStylistRequest);
    }

    // Other methods...
}
