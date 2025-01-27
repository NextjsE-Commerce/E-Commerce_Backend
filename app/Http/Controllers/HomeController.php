<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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

        $token = $user->createToken( $user->id )->plainTextToken;

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

            // $userCart = Cart::where("user_id", $user->id)->get()
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

    public function UpdateProduct(Request $req, $id)
    {
     
            $validatepro = Validator::make($req->all(), [
                'product_name' => 'required|String|max:50',
                'category' => 'required|String|max:50',
                'description' => 'required|String',
                'product_status' => 'required|String',
                'quantity' => 'required|string|max:10',
                'price' => 'required|string|min:4',
            ]);

            if ($validatepro->fails()) {
                return response()->json([
                    'validate_err' => $validatepro->messages(),
                ], 422);
            }

            $updproduct = Product::find($id);

            $categoryId = Category::where("category_name", $req->category)->first();

            $updproduct->product_name = $req->product_name;
            $updproduct->category_id = $categoryId->id;
            $updproduct->description = $req->description;
            $updproduct->product_status = $req->product_status;
            $updproduct->quantity = $req->quantity;
            $updproduct->price = $req->price;

            if ($req->hasFile('product_image')) {
                $image = $req->file('product_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('Products'), $imageName);
                $updproduct->product_image = $imageName;
            } else {
                $updproduct->product_image = $updproduct->product_image;
            }

            $updproduct->save();

            return response()->json([
                'status' => 200,
                'message' => 'Product Updated Successfully!',
            ]);
    }


    public function Products()
    {
        $products = Product::where('quantity', '>', 0)->orderBy('id', 'desc')->get();
        $categories = Category::pluck('category_name', 'id');


        $products = $products->map(function ($product) use ($categories) {
            $product->category = $categories[$product->category_id] ?? 'No Category';
            return $product;
        });

        return response()->json([
            'status' => 200,
            'products' => $products,
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

    public function AddtoCart(Request $request)
    {

        $validatecart = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validatecart->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validatecart->errors(),
            ], 400);
        }

        $user = Auth::user();
        $product = Product::find($request->product_id);

        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();


        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->price = $product->price * $cart->quantity;
            $cart->image = $product->product_image;
            $cart->save();
        } else {
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->product_id = $product->id;
            $cart->quantity = $request->quantity;
            $cart->price = $product->price * $request->quantity;
            $cart->image = $product->product_image;
            $cart->save();
        }

        $itemsInCart = Cart::where('user_id', $user->id)->count();
        $userCarts = Cart::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart successfully!',
            'items_in_cart' => $itemsInCart,
            'cart' => $userCarts,
        ], 200);
    }

    public function DeleteCart($id)
    {
        $user = Auth::user();

        $data = Cart::find($id);
        $data->delete();

        $itemsInCart = Cart::where('user_id', $user->id)->count();

        return response()->json([
            'status' => 200,
            'items_in_cart' => $itemsInCart,
            'message' => "Product delated Successfully"
        ]);
    }

    public function getItemInCart(Request $request)
    {
        $user = Auth::user();

        $itemsInCart = Cart::where('user_id', $user->id)->count();


        return response()->json(
            [
                'items_in_cart' => $itemsInCart
            ],
            200
        );
    }

    public function userCart(Request $req)
    {
        $user = Auth::user();
        $usercart = Cart::where('user_id', $user->id)->orderBy('id', 'desc')
            ->get()
            ->map(function ($cartItem,) {
                $product = Product::find($cartItem->product_id);
                $cartItem->product_name = $product->product_name;
                $cartItem->product_status = $product->product_status;
                return $cartItem;
            });

        return response()->json([
            'usercart' => $usercart
        ], 200);
    }

    public function updateCart(Request $request, $id)
    {
        try {
            $cart = Cart::findOrFail($id);
            $cart->quantity = $request->quantity;
            $cart->price = $request->price;
            $cart->save();

            return response()->json([
                'message' => 'Cart updated successfully!',
                'cart' => $cart,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating cart!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function productDetail($id)
    {

        $productdetail = Product::find($id);

        if (!$productdetail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'productdetail' => $productdetail
        ], 200);
    }

    // public function productDetailAdmin($id)
    // {

    //     $productdetailadmin = Product::where("id",$id)
    //     ->get()
    //     ->map(function ($Item,) {
    //         $catgory = Category::find($Item->category_id);
    //         $Item->category = $catgory->category_name;
    //         return $Item;
    //     });

    //     if (!$productdetailadmin) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Product not found'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'productdetailadmin' => $productdetailadmin
    //     ], 200);
    // }

//     public function productDetailAdmin($id)
//    {
//     $productdetailadmin = Product::where("id",$id)
//     ->get()
//     ->map(function ($Item) {
//             $category = Category::find($Item->category_id);
//             $Item->category = $category ? $category->category_name : null; // Handle case where category is not found
//             return $Item;
//         });

//     if (!$productdetailadmin) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Product not found'
//         ], 404);
//     }

//     return response()->json([
//         'status' => 'success',
//         'productdetailadmin' => $productdetailadmin
//     ], 200);
// }

public function productDetailAdmin($id)
{
    // Fetch the product by ID
    $product = Product::where("id", $id)->first();

    // Check if the product exists
    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }

    // Attach the category name to the product
    $category = Category::find($product->category_id);
    $product->category = $category ? $category->category_name : null;

    // Return the product in JSON format
    return response()->json([
        'status' => 'success',
        'productdetailadmin' => $product
    ], 200);
}

}
