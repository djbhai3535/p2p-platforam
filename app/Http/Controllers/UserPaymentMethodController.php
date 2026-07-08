<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\UserPaymentMethod;
use Illuminate\Http\Request;

class UserPaymentMethodController extends Controller
{
    /**
     * Display user's linked payment accounts.
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $linkedMethods = $request->user()->userPaymentMethods()->with('paymentMethod')->get();
        $availableMethods = PaymentMethod::where('country_id', $request->user()->country_id)
            ->where('is_active', true)
            ->get();

        return view('profile.payment-methods', compact('linkedMethods', 'availableMethods'));
    }

    /**
     * Store linked payment option.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'account_title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'array'],
        ]);

        $pm = PaymentMethod::findOrFail($request->payment_method_id);
        
        // Dynamically validate payment fields
        $fields = $pm->fields ?? [];
        $validatedDetails = [];
        
        foreach ($fields as $field) {
            $name = $field['name'];
            $label = $field['label'];
            $isRequired = $field['required'] ?? false;
            
            if ($isRequired && empty($request->input("details.{$name}"))) {
                return back()->withErrors(["details.{$name}" => "The {$label} field is required."])->withInput();
            }

            $validatedDetails[$name] = $request->input("details.{$name}");
        }

        // Store
        UserPaymentMethod::create([
            'user_id' => $request->user()->id,
            'payment_method_id' => $pm->id,
            'account_title' => $request->account_title,
            'account_details' => $validatedDetails,
            'is_active' => true,
        ]);

        return redirect()->route('profile.payment-methods')->with('status', 'Payment method linked successfully.');
    }

    /**
     * Delete a user linked payment account.
     */
    public function destroy(UserPaymentMethod $paymentMethod, Request $request): \Illuminate\Http\RedirectResponse
    {
        // Enforce ownership
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403);
        }

        $paymentMethod->delete();

        return redirect()->route('profile.payment-methods')->with('status', 'Payment method removed successfully.');
    }
}
