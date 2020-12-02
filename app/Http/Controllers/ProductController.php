<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Image;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //frontend
    public function getListProduct(){
        $category_id = request()->get('category');
        if($category_id!=null){
            $listProduct = Product::where('category_id',$category_id)->where('enabled',1)->paginate(9);
        }
        else{
            $key = request()->get('search');   
            if($key!=null){
                $listProduct = Product::where('name','like','%'.$key.'%')->where('enabled',1)->paginate(9);
            }
            else $listProduct = Product::where('enabled',1)->paginate(9);
        }
        return view('frontend.product',['listProduct'=>$listProduct]);
    }
    public function getDetailProduct($id){
        $product_detail = Product::find($id);
        $listReview = Review::whereHas('order_item',function($query) use($id){
            $query->where(['product_id'=>$id,'reviewed'=>1]); 
        })->get();
        $product_detail->views ++;
        $product_detail->save();
        return view('frontend.product_detail',['product_detail'=>$product_detail,'listReview'=>$listReview]);
    }
    public function postSearchProduct(Request $request){
        $key = $request->key;    
        return redirect(url('/product?search='.$key));
    }
    //backend
    public function getList(){
    	$listProduct = Product::all(); 
    	return view('backend.product.list',['listProduct'=>$listProduct]);
    }
    public function getAdd(){
    	$listCategory = Category::all();
    	return view('backend.product.add_edit',['listCategory'=>$listCategory]);
    }
    public function postAdd(Request $request){
        $request->validate([
            'stringDescription' => 'max:2000',
            'intPromotion_price' => 'lt:intPrice',
            'images' => 'mimes:jpeg,jpg,png'
        ],
        [
            'max' => 'Trường trên tối đa :max ký tự',
            'lt' => 'Giá ưu đãi phải nhỏ hơn giá thường',
            'mimes' => 'Ảnh định dạng jpeg,jpg,png'
        ]);

    	$product = new Product;
    	$product->category_id = $request->intCategory_id;
    	$product->name = $request->stringName;
    	$product->brand = $request->stringBrand;
    	$product->origin = $request->stringOrigin;
    	$product->price = $request->intPrice;
    	$product->promotion_price = $request->intPromotion_price;
    	$product->description =($request->stringDescription!="")?$request->stringDescription:"";
    	$product->enabled = $request->intEnabled;
    	$product->quantity_in_stock = $request->intQuantity_in_stock;
    	$product->views = 0;
    	#save product vào csdl
    	$product->save();

    	$images = $request->file('images');
    	foreach ($images as $index => $image ) {
    		# code...
    		$imageName = time().'ProductId'.$product->id.'ImageId'.$index.'.png';
    		$image->move('images/product',$imageName);

    		$dbImage = new Image;
    		$dbImage->name = $imageName;
    		$dbImage->product_id = $product->id;
    		$dbImage->save();
    	}
    	return redirect(url('/admin-page/product/list'))->with(['typeMsg'=>'success','msg'=>'Thêm thành công']);
    }
    public function getEdit($id){
        $listCategory = Category::all();
        $product = Product::find($id);
        return view('backend.product.add_edit',['listCategory'=>$listCategory,'product'=>$product]);
    }
    public function postEdit(Request $request,$id){
        $request->validate([
            'stringDescription' => 'max:2000',
            'intPromotion_price' => 'lt:intPrice',
            'images' => 'mimes:jpeg,jpg,png'
        ],
        [
            'max' => 'Trường trên tối đa :max ký tự',
            'lt' => 'Giá ưu đãi phải nhỏ hơn giá thường',
            'mimes' => 'Ảnh định dạng jpeg,jpg,png'
        ]);

    	$listCategory = Category::all();
        $product = Product::find($id);
        $product->name = $request->stringName;
        $product->brand = $request->stringBrand;
        $product->origin = $request->stringOrigin;
        $product->price = $request->intPrice;
        $product->promotion_price = $request->intPromotion_price;
        $product->description = $request->stringDescription;
        $product->enabled = $request->intEnabled;
        $product->quantity_in_stock = $request->intQuantity_in_stock;
        #save product vào csdl
        $product->save();

        if($request->hasFile('images')){
            #xóa ảnh cũ
            $oldImages = Image::where('product_id',$id)->get();
            foreach ($oldImages as $oldImage ) {
                if(file_exists('images/product/'.$oldImage->name)) 
                    unlink('images/product/'.$oldImage->name);
            }
            Image::where('product_id',$id)->delete();
            #thêm ảnh mới
            $images = $request->file('images');
            foreach ($images as $index => $image ) {
                # code...
                $imageName = time().'ProductId'.$product->id.'ImageId'.$index.'.png';
                $image->move('images/product',$imageName);

                $dbImage = new Image;
                $dbImage->name = $imageName;
                $dbImage->product_id = $product->id;
                $dbImage->save();
            }
        }
        return redirect(url('/admin-page/product/list'))->with(['typeMsg'=>'success','msg'=>'Sửa thành công']);
    }
    public function getDelete($id){
        $oldImage = Image::where('product_id',$id)->get();
        foreach ($oldImages as $oldImage ) {
            if(file_exists('images/product/'.$oldImage->name)) 
                unlink('images/product/'.$oldImage->name);
        }
        Product::destroy($id);
        return redirect(url('/admin-page/product/list'))->with(['typeMsg'=>'success','msg'=>'Xóa thành công']);
    }
}
