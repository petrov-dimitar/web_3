<?php

use App\Recipe;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', function (Request $request) {

    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required',
        'c_password' => 'required|same:password',
    ]);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 401);
    }
    $input = $request->all();
    $input['password'] = bcrypt($input['password']);
    $input['api_token'] = str_random(80);
    $user = User::create($input);

    return response()->json(['success' => $user]);
});

Route::post('login', function (Request $request) {

    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {

        return response()->json(['user' => Auth::user()], 200);
    } else {
        return  response()->json(['error' => 'invalid'], 401);
    };
});

Route::get('users', function () {

    return User::all();
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->put('/user/update', function (Request $request) {
    $user = App\User::where('api_token', '=', $request->api_token)->first();

    $user->name = request('name');

    $user->save();

    return response()->json(['user' => $user], 200);
});

Route::middleware('auth:api')->put('/user/delete', function (Request $request) {
    $user = App\User::where('api_token', '=', $request->api_token)->first();

    $user->delete();

    return response()->json(['user deleted' => $user], 200);
});


Route::middleware('auth:api')->get('/recipes/user', function (Request $request) {

    $user = $request->user();

    if ($user) {
        if ($user->firstOrFail()->Recipes()->count() > 0) {
            return response()->json(['user' => $user->Recipes()->get()], 200);
        } else {
            return  response()->json(['error' => 'No Recipes Yet'], 200);
        };
    } else {
        return  response()->json(['error' => 'invalid'], 500);
    }
});



Route::middleware('auth:api')->post('/user/recipes/create', function (Request $request) {
    $user = App\User::where('api_token', '=', $request->api_token)->first();
    $recipe = new Recipe();

    $recipe->recipe_name = $request->recipe_name;
    $recipe->description = $request->description;
    $recipe->user_id = $user->id;

    $recipe->save();

    return response()->json(['recipe created' => $recipe], 200);
});

Route::middleware('auth:api')->put('/recipe/update', function (Request $request) {

    $recipe = App\Recipe::find($request->recipe_id);


    $recipe->id = $request->recipe_id;
    $recipe->recipe_name = $request->recipe_name;
    $recipe->description = $request->description;

    $recipe->save();

    return response()->json(['recipe' => $recipe], 200);
});

Route::middleware('auth:api')->put('/user/recipe/delete', function (Request $request) {

    $recipe = App\Recipe::find($request->id);
    $recipe->delete();

    return response()->json(['recipe deleted' => $recipe], 200);
});

Route::get('recipes', function () {

    return Recipe::all();
});
