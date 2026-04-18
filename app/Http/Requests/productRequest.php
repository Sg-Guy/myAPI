<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class productRequest extends FormRequest
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

        $rules = [
        'nom'=>'required' ,
        'category_id'=>'required|exists:categories,id',//|exists:categories,id' ,
        'description'=>'nullable' ,
        'stock'=>'required|integer|min:10',
        'prix_unitaire'=>'required|numeric' ,
        'prix_promo'=>'nullable|numeric|lt:prix_unitaire' ,
        'image|mimes:jpg,jpeg,png|max:2048'
        ];

        //Si la methode est update , le champ image n'est pas required
        if ($this->isMethod('post')){
            $rules['image'] =  'required|image|mimes:jpg,jpeg,png|max:2048' ;
        }

        return $rules ;
    }

    public function messages()
    {
        return [
            'nom.required' =>"Nom du produit requis" , 

            'category_id.required' =>"Catégorie requise" , 

            'category_id.exists' =>"Catégorie inexistante" , 

            'stock.required' =>"Quantité requise requis" , 

            'stock.integer' =>"Vueilleez renseigner un nombre entier" , 

            'stock.min' =>"la quantité disponible doit être supérieur  ou égale à 10" , 

            'prix_unitaire.required'=>"Etrez le prix de vente du produit" ,

            'prix_unitaire.numeric'=>"numeric" ,

            //'prix_promo.required'=>"requis" ,

            'prix_promo.numeric'=>"numeric" ,

            'prix_promo.lt'=>"Ce prix doit être inférieur au prix normal" ,

            'image.required'=>"Veuillez choisir une image pour le produit" ,
            ] ;
    }


    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            "message" => "Validatin échouée" , 
            "errors" => $validator->errors() 
        ] , 422);

        throw new ValidationException($validator , $response) ;
    }
}
