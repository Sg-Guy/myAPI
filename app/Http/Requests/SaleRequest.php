<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'products' => 'required|array|min:1', // Liste des produits
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'products.required' => 'Vous devez fournir au moins un produit.',
            'products.*.product_id.exists' => 'Le produit sélectionné n’existe pas.',
            'products.*.quantity.min' => 'La quantité pour un produit doit être d’au moins 1.',
            'products.*.quantity.required' => 'La quantité d’un produit est obligatoire.',
        ]; 
    }
}
