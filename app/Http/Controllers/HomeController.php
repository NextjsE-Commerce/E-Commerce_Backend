<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    private $user;

    public function Register(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'firstName' => 'required|String|max:50',
            'lastName' => 'required|String|max:50',
            'sex' => 'required|String|max:50',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:10',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'validate_err' => $validate->messages(),
            ], 422);
        }

        $user = new User();
        $user->firstName = $req->firstName;
        $user->lastName = $req->lastName;
        $user->sex = $req->sex;
        $user->role = 'user';
        $user->email = $req->email;
        $user->phone = $req->phone;
        $user->password = Hash::make($req->password);


        if ($req->hasFile('profilePicture')) {
            $image = $req->file('profilePicture');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('Users'), $imageName);

            $user->profile_picture = $imageName;
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'User Registered Successfully!',
            'token_type' => 'Bearer',
            'token' => $user->createToken('API Token')->plainTextToken,
            // 'result' => $user
        ]);
    }



    public function Login(Request $req)
    {
        // Validate input fields
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $req->email)->first();

        if (!$user) {
            return response()->json([
                "status" => 404,
                "Login_error" => "No such user found!",
            ]);
        }

        if (!Hash::check($req->password, $user->password)) {
            return response()->json([
                "status" => 401,
                "Login_error" => "Email or password is incorrect!",
            ]);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'User Logged in Successfully!',
            'token_type' => 'Bearer',
            'token' => $token,
            'role' => $user->role
        ]);
    }

    public function Logout()
    {


        auth()->user()->tokens()->delete();

        // return response()->json([
        //     'status' => 200,
        // ]);

        return response()->json(['status' => 200, 'message' => 'Logged out successfully'])
            ->withCookie(cookie('access_token', '', -1));
    }


    public function getUserData()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json([
                'status' => 200,
                'user' => $user,
                'role' => $user->role,
            ]);
        }

        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized!',
        ], 401);
    }


    public function Addproduct(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'product_name' => 'required|String|max:50',
            'category' => 'required|String|max:50',
            'description' => 'required|String',
            'product_status' => 'required|String',
            'quantity' => 'required|string|max:10',
            'price' => 'required|string|min:4',
            'product_image' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'validate_err' => $validate->messages(),
            ], 422);
        }

        $product = new Product();

        $categoryId = Category::where("category_name", $req->category)->first();

        $product->product_name = $req->product_name;
        $product->category_id = $categoryId->id;
        $product->description = $req->description;
        $product->product_status = $req->product_status;
        $product->quantity = $req->quantity;
        $product->price = $req->price;


        if ($req->hasFile('product_image')) {
            $image = $req->file('product_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('Products'), $imageName);

            $product->product_image = $imageName;
        }

        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Product Added Successfully!',
        ]);
    }

    public function Products()
    {
        //   $product = Product::where('quantity', '>', 0)->paginate(9);
        $product = Product::where('quantity', '>', 0)->orderBy('id', 'desc')->get();
        $category = Category::orderBy('category_name', 'asc')->get();

        return response()->json([
            'status' => 200,
            'products' => $product,
            'category' => $category,
        ]);
    }

    public function Newproduct()
    {
        //   $product = Product::where('quantity', '>', 0)->paginate(9);
        $product = Product::where('quantity', '>', 0)->where('product_status', 'New')->get();
        $category = Category::orderBy('category_name', 'asc')->get();

        return response()->json([
            'status' => 200,
            'products' => $product,
            'category' => $category,
        ]);
    }

    public function Deleteproduct($id)
    {

        $data = Product::find($id);
        $data->delete();

        return response()->json([
            'status' => 200,
            'message' => "Product delated Successfully"
        ]);
    }
}
