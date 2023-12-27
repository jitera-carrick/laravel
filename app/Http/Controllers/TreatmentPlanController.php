<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Models\Stylist;
use App\Models\User;
use App\Models\Message;
use App\Mail\TreatmentPlanCancelled;
use App\Mail\TreatmentPlanNotification;
use App\Mail\TalkRoomNewMessage;
use App\Mail\TreatmentPlanFixedCustomer;
use App\Mail\TreatmentPlanFixedStylist;
use App\Mail\TreatmentPlanFixedOwner;
use App\Mail\TreatmentPlanDeclinedStylist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TreatmentPlanResource;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreatmentPlanController extends Controller
{
    // ... (other methods in the controller)

    public function createTreatmentPlan(Request $request)
    {
        // ... (existing code for createTreatmentPlan)
    }

    public function approveTreatmentPlan(Request $request)
    {
        // ... (existing code for approveTreatmentPlan)
    }

    // New method sendMessageAndCreateProvisionalReservation
    public function sendMessageAndCreateProvisionalReservation(Request $request)
    {
        // ... (new code for sendMessageAndCreateProvisionalReservation)
    }

    // ... (other methods in the controller)

    // ... (rest of the existing code)
}
