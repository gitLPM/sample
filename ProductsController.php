<?php
namespace App\Http\Controllers\Web;

//validator is builtin class in laravel
use App\Models\Web\Currency;
use App\Models\Web\Index;
//for password encryption or hash protected
use App\Models\Web\Languages;
//for authenitcate login data
use App\Models\Web\Products;
use Auth;
use App\Models\Core\Categories;
//for requesting a value
use DB;
//for Carbon a value
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lang;
use Session;
use App\Models\Core\Footerlink; //ZST Add
use App\Models\Core\Milestone; // ZST Add

//email

// added aug 4 2022
final class PCC {

  public static function p($val=null)
  {
      print("<pre>" . print_r($val, true) . "</pre>");
  }

  public static function pp($name='', $val=null)
  {
      print("<pre> $name :: " . print_r($val, true) . "</pre>");
  }

  public static function getDate($start_date="", $modify="+60 days") {
    $date = new \DateTime($start_date);
    $date ->modify($modify);
    return $date ->format("Y-m-d");
  }

}

class ProductsController extends Controller
{
    public function __construct(
        Index $index,
        Languages $languages,
        Products $products,
        Currency $currency,
        Footerlink $footerlink // ZST Add
    ) {
        $this->index = $index;
        $this->languages = $languages;
        $this->products = $products;
        $this->currencies = $currency;
        $this->theme = new ThemeController();
        $this->footerlink = $footerlink;  // ZST Add
    }

    public function reviews(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            $check = DB::table('reviews')
                ->where('customers_id', Auth::guard('customer')->user()->id)
                ->where('products_id', $request->products_id)
                ->first();

            if ($check) {
                return 'already_commented';
            }
            $id = DB::table('reviews')->insertGetId([
                'products_id' => $request->products_id,
                'reviews_rating' => $request->rating,
                'customers_id' => Auth::guard('customer')->user()->id,
                'customers_name' => Auth::guard('customer')->user()->first_name,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            DB::table('reviews_description')
                ->insert([
                    'review_id' => $id,
                    'language_id' => Session::get('language_id'),
                    'reviews_text' => $request->reviews_text,
                ]);
            return 'done';
        } else {
            return 'not_login';

        }
    }


    //shop
    public function shop(Request $request)
    {
        $title = array('pageTitle' => Lang::get('website.Shop'));
        $result = array();

        $result['fetch_footer_link'] = $this->footerlink->footer_link();
        $result['commonContent'] = $this->index->commonContent();
        // $final_theme = $this->theme->theme();

        //liked products
        // $result['liked_products'] = $this->products->likedProducts();
        $result['categories'] = $this->products->categories();

        // Show New Fragrances category
        $SubCategory = '';
        $store = array();
        $SubCategory = DB::table('categories')
                ->where('latest', '1')
                ->get();


        if(!empty($SubCategory)){
            foreach($SubCategory as $list){

                $CatName = DB::table('categories_description')
                ->where('categories_id', $list->categories_id)
                ->first();

                $Catslug = DB::table('categories')
                ->where('categories_id', $list->parent_id)
                ->first();

                /*
                * start for 60 old check...
                *
                */

                $category_date = date("Y-m-d", strtotime($list->last_modified));

                $curr_date = date("Y-m-d");
                $expiry_date = PCC::getDate($category_date, "+60 days"); // aug 4.... oct 3

                // PCC::pp('categories_id', $list->parent_id);
                // PCC::pp('categories name', $CatName->categories_name);
                // PCC::pp('category_date', date("F j, Y", strtotime($category_date)) );
                // PCC::pp('expiry date', date("F j, Y", strtotime($expiry_date)) );
                // PCC::p('--------------------------------');


                if ($expiry_date >= $curr_date):

                $store[] = array(

                'catname' => $CatName->categories_name,
                'Patslug' => $Catslug->categories_slug,
                'catslug' => $list->categories_slug,

                );

                endif;

            }
        }

        //exit;

        return view("web.shop", ['title' => $title, 'SubCategory' => $store, ])->with('result', $result);

    }

    public function NewFrangrance(Request $request)
    {
        $title = array('pageTitle' => Lang::get("New Frangrance"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();

            $SubCategory = DB::table('categories')
            ->leftJoin('image_categories', 'categories.categories_icon', '=', 'image_categories.image_id')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->select(
              'categories.categories_id',
              'image_categories.path as image_path',
              'categories.categories_slug as slug',
              'categories_description.categories_name',
              'categories.parent_id',
              'categories.last_modified as date'
              )
            ->where('categories_description.language_id', '=', Session::get('language_id'))
            ->where('categories.categories_status', 1)
            ->where('categories.latest', 1)
            ->groupBy('categories.categories_id')
            ->get();

            /*
            * start for 60 old check...
            *
            */

            $store = [];

            if(!empty($SubCategory)):
              foreach($SubCategory as $item):

            $category_date = date("Y-m-d", strtotime($item->date));

            $curr_date = date("Y-m-d");
            $expiry_date = PCC::getDate($category_date, "+60 days"); // aug 4.... oct 3


            if ($expiry_date >= $curr_date):
              $store[] = $item;

            /*  PCC::pp('categories_id', $item->categories_id);
                PCC::pp('categories name', $item->categories_name);
                PCC::pp('category_date', date("F j, Y", strtotime($category_date)) );
                PCC::pp('expiry date', date("F j, Y", strtotime($expiry_date)) );
                PCC::p('--------------------------------');
             */

            endif;

            endforeach;
          endif;

          //exit;


          /*
          * end for 60 old check...
          *
          */

        return view("web.newfrangrance",['title' => $title, 'SubCategory' => $store])->with('result', $result);
    }

    public function AllProducts(Request $request)
    {
        $title = array('pageTitle' => Lang::get("New Frangrance"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
            $currentDate = time();
            $productsdata = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            // ->leftJoin('image_categories', 'image_categories.image_id', '=', 'products.products_image')
            ->where('products.products_status', '=', '1')->get();
        return view("web.products",['title' => $title, 'productsdata' => $productsdata])->with('result', $result);
    }


    public function filterProducts(Request $request)
    {

        //min_price
        if (!empty($request->min_price)) {
            $min_price = $request->min_price;
        } else {
            $min_price = '';
        }

        //max_price
        if (!empty($request->max_price)) {
            $max_price = $request->max_price;
        } else {
            $max_price = '';
        }

        if (!empty($request->limit)) {
            $limit = $request->limit;
        } else {
            $limit = 15;
        }

        if (!empty($request->type)) {
            $type = $request->type;
        } else {
            $type = '';
        }

        //if(!empty($request->category_id)){
        if (!empty($request->category) and $request->category != 'all') {
            $category = DB::table('categories')->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')->where('categories_slug', $request->category)->where('language_id', Session::get('language_id'))->get();

            $categories_id = $category[0]->categories_id;
        } else {
            $categories_id = '';
        }

        //search value
        if (!empty($request->search)) {
            $search = $request->search;
        } else {
            $search = '';
        }

        //min_price
        if (!empty($request->min_price)) {
            $min_price = $request->min_price;
        } else {
            $min_price = '';
        }

        //max_price
        if (!empty($request->max_price)) {
            $max_price = $request->max_price;
        } else {
            $max_price = '';
        }

        if (!empty($request->filters_applied) and $request->filters_applied == 1) {
            $filters['options_count'] = count($request->options_value);
            $filters['options'] = $request->options;
            $filters['option_value'] = $request->options_value;
        } else {
            $filters = array();
        }

        $data = array('page_number' => $request->page_number, 'type' => $type, 'limit' => $limit, 'categories_id' => $categories_id, 'search' => $search, 'filters' => $filters, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $products = $this->products->products($data);
        $result['products'] = $products;

        $cart = '';
        $result['cartArray'] = $this->products->cartIdArray($cart);
        $result['limit'] = $limit;
        $result['fetch_footer_link'] = $this->footerlink->footer_link();
        $result['commonContent'] = $this->index->commonContent();
        return view("web.filterproducts")->with('result', $result);

    }

    public function ModalShow(Request $request)
    {
        $result = array();
        $result['fetch_footer_link'] = $this->footerlink->footer_link();
        $result['commonContent'] = $this->index->commonContent();
        $final_theme = $this->theme->theme();
        //min_price
        if (!empty($request->min_price)) {
            $min_price = $request->min_price;
        } else {
            $min_price = '';
        }

        //max_price
        if (!empty($request->max_price)) {
            $max_price = $request->max_price;
        } else {
            $max_price = '';
        }

        if (!empty($request->limit)) {
            $limit = $request->limit;
        } else {
            $limit = 15;
        }

        $products = $this->products->getProductsById($request->products_id);

        $products = $this->products->getProductsBySlug($products[0]->products_slug);
        //category
        $category = $this->products->getCategoryByParent($products[0]->products_id);

        if (!empty($category) and count($category) > 0) {
            $category_slug = $category[0]->categories_slug;
            $category_name = $category[0]->categories_name;
        } else {
            $category_slug = '';
            $category_name = '';
        }
        $sub_category = $this->products->getSubCategoryByParent($products[0]->products_id);

        if (!empty($sub_category) and count($sub_category) > 0) {
            $sub_category_name = $sub_category[0]->categories_name;
            $sub_category_slug = $sub_category[0]->categories_slug;
        } else {
            $sub_category_name = '';
            $sub_category_slug = '';
        }

        $result['category_name'] = $category_name;
        $result['category_slug'] = $category_slug;
        $result['sub_category_name'] = $sub_category_name;
        $result['sub_category_slug'] = $sub_category_slug;

        $isFlash = $this->products->getFlashSale($products[0]->products_id);

        if (!empty($isFlash) and count($isFlash) > 0) {
            $type = "flashsale";
        } else {
            $type = "";
        }

        $data = array('page_number' => '0', 'type' => $type, 'products_id' => $products[0]->products_id, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $detail = $this->products->products($data);
        $result['detail'] = $detail;
        $postCategoryId = '';
        if (!empty($result['detail']['product_data'][0]->categories) and count($result['detail']['product_data'][0]->categories) > 0) {
            $i = 0;
            foreach ($result['detail']['product_data'][0]->categories as $postCategory) {
                if ($i == 0) {
                    $postCategoryId = $postCategory->categories_id;
                    $i++;
                }
            }
        }

        $data = array('page_number' => '0', 'type' => '', 'categories_id' => $postCategoryId, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $simliar_products = $this->products->products($data);
        $result['simliar_products'] = $simliar_products;

        $cart = '';
        $result['cartArray'] = $this->products->cartIdArray($cart);

        //liked products
        $result['liked_products'] = $this->products->likedProducts();
        return view("web.common.modal1")->with('result', $result);
    }

    //access object for custom pagination
    public function accessObjectArray($var)
    {
        return $var;
    }

     //productDetail
     public function productDetail(Request $request)
     {
		// ZST Add From 
        $searchdata = '';
        if($request->searchcat){
            $searchdata = $request->searchcat;
        }
        // ZST Add End
        session(['step' => '2']);

         $title = array('pageTitle' => Lang::get('website.Product Detail'));
         $result = array();
         $result['fetch_footer_link'] = $this->footerlink->footer_link();
         $result['commonContent'] = $this->index->commonContent();
        //  $final_theme = $this->theme->theme();
         //min_price
         if (!empty($request->min_price)) {
             $min_price = $request->min_price;
         } else {
             $min_price = '';
         }

         //max_price
         if (!empty($request->max_price)) {
             $max_price = $request->max_price;
         } else {
             $max_price = '';
         }

         if (!empty($request->limit)) {
             $limit = $request->limit;
         } else {
             $limit = 15;
         }

         $products = $this->products->getProductsBySlug($request->slug);
         if(!empty($products) and count($products)>0){

             //category
             $category = $this->products->getCategoryByParent($products[0]->products_id);

             if (!empty($category) and count($category) > 0) {
                 $category_slug = $category[0]->categories_slug;
                 $category_name = $category[0]->categories_name;
             } else {
                 $category_slug = '';
                 $category_name = '';
             }
             $sub_category = $this->products->getSubCategoryByParent($products[0]->products_id);
            //  print_r( $sub_category);

            //  dd( Session::get('products_category'));
             if (!empty( Session::get('products_category'))) {
                 $sub_category_name = Session::get('products_category_name');
                 $sub_category_slug = Session::get('products_category');
             } else {
                 $sub_category_name = '';
                 $sub_category_slug = '';
             }

             if (!empty( Session::get('parent_pro_category_slug'))) {
                $category_name = Session::get('parent_pro_category_name');
                $category_slug = Session::get('parent_pro_category_slug');
            } else {
                $category_name = '';
                $category_slug = '';
            }

             $result['category_name'] = $category_name;
             $result['category_slug'] = $category_slug;
             $result['sub_category_name'] = $sub_category_name;
             $result['sub_category_slug'] = $sub_category_slug;

             $isFlash = $this->products->getFlashSale($products[0]->products_id);

             if (!empty($isFlash) and count($isFlash) > 0) {
                 $type = "flashsale";
             } else {
                 $type = "";
             }
             $postCategoryId = '';
             $data = array('page_number' => '0', 'type' => $type, 'products_id' => $products[0]->products_id, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
             $detail = $this->products->products($data);
             $result['detail'] = $detail;
             if (!empty($result['detail']['product_data'][0]->categories) and count($result['detail']['product_data'][0]->categories) > 0) {
                 $i = 0;
                 foreach ($result['detail']['product_data'][0]->categories as $postCategory) {
                     if ($i == 0) {
                         $postCategoryId = $postCategory->categories_id;
                         $i++;
                     }
                 }
             }

             $data = array('page_number' => '0', 'type' => '', 'categories_id' => $postCategoryId, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
             $simliar_products = $this->products->products($data);
             $result['simliar_products'] = $simliar_products;

             $cart = '';
             $result['cartArray'] = $this->products->cartIdArray($cart);

             //liked products
             $result['liked_products'] = $this->products->likedProducts();

             $data = array('page_number' => '0', 'type' => 'topseller', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
             $top_seller = $this->products->products($data);
             $result['top_seller'] = $top_seller;
         }else{
             $products = '';
             $result['detail']['product_data'] = '';
         }
         $SubCategory = '';
         $store = array();
         $SubCategory = DB::table('categories')
                 ->where('latest', '1')
                 ->get();
         //}
         if(!empty($SubCategory)){
             foreach($SubCategory as $list){
                 $CatName = DB::table('categories_description')
                 ->where('categories_id', $list->categories_id)
                 ->first();
                 $Catslug = DB::table('categories')
                 ->where('categories_id', $list->parent_id)
                 ->first();
                 $store[] = array('catname' => $CatName->categories_name, 'Patslug' => $Catslug->categories_slug, 'catslug' => $list->categories_slug);
             }
         }
         $currency_symbol = session('symbol_left') ? session('symbol_left') : session('symbol_right') ;
         $currency  = DB::table('currencies')->where('symbol_left',$currency_symbol)->orwhere('symbol_right',$currency_symbol)->first();
         $result['currency_value'] = $currency ? $currency->value : 1;

         $get_category_slug = DB::table('categories')
         ->where('categories_slug', $category_slug)
         ->first();

         $getSubCategories = DB::table('categories')
         ->where('parent_id', $get_category_slug->categories_id)
         ->paginate(6);

        //  load more category
        $SubCategories = array();
            $SubCategories = DB::table('categories')
                ->where('parent_id', $get_category_slug->categories_id)
                ->paginate(10);
            if ($request->ajax()) { //dd($request->all());
                $html = '';
                foreach($SubCategories as $list) {
                    $CatName = DB::table('categories_description')
                    ->where('categories_id', $list->categories_id)
                    ->first();
                    $url = url('category/'.$category_slug.'/'.$list->categories_slug);
                    
					//if($CatName->categories_name!="")
                    $ds =$CatName->categories_name;
                   //if($CatName->categories_name=="NAJ 812 inspired by 360 Degrees Type")
                   $word = $searchdata;
                   $mystring = $CatName->categories_name;                   
                    
                //ZST Add From
                   if($searchdata!="")
                   {
                   if(strpos($mystring, $word) !== false ) {
                    $html.="<li class='col-md-6'><a href=$url>$CatName->categories_name</a></li>";
                   }
                }
                else {
                    $html.="<li class='col-md-6'><a href=$url>$CatName->categories_name</a></li>";
                }
               //ZST Add End
                }
                return $html;
            }

        //  dd($getSubCategories);
         return view("web.detail", ['title' => $title, 'SubCategory' => $store, 'SubCategories' => $SubCategories, 'getSubCategories'=> $getSubCategories, 'searchdata' => $searchdata])->with('result', $result);
     }

    //filters
    public function filters($data)
    {
        $response = $this->products->filters($data);
        return ($response);
    }

    //getquantity
    public function getquantity(Request $request)
    {
        $data = array();
        $data['products_id'] = $request->products_id;
        $data['attributes'] = $request->attributeid;

        $result = $this->products->productQuantity($data);
        print_r(json_encode($result));
    }

    // Add BY Mahavir
    public function productCategory(Request $request)
    {
        $searchdata = '';
            if($request->searchcat){
                $searchdata = $request->searchcat;
            }

        // if(isset($request->slug) && !empty($request->slug)){
            $SubCategories = array();
            $categories = DB::table('categories')
                    ->where('categories_slug', $request->slug)
                    ->first();
            if($categories){
                $SubCategories = DB::table('categories')
                    ->where('parent_id', $categories->categories_id)
                    ->paginate(10);
            }
            $CategoriesTitle = DB::table('categories_description')
                    ->where('categories_id',$categories->categories_id)
                    ->first();
            $title = array('pageTitle' => Lang::get("website.View Cart"));
            $result = array();
            $data = array();

            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();

            if ($request->ajax()) { //dd($request->all());
                $html = '';
                if($request->searchcat){
                   $param = $request->searchcat;
                    $categoriesnew = Categories::sortable(['categories_id'=>'ASC'])
                    ->leftJoin('categories_description','categories_description.categories_id', '=', 'categories.categories_id')
                        ->leftJoin('images','images.id', '=', 'categories.categories_image')
                        ->leftJoin('image_categories as categoryTable','categoryTable.image_id', '=', 'categories.categories_image')
                        ->leftJoin('image_categories as iconTable','iconTable.image_id', '=', 'categories.categories_icon')
                        ->select('categories.categories_id as id', 'categories.parent_id as parent_id','categories.categories_slug as categories_slug', 'categories.categories_image as image',
                        'categories.categories_icon as icon',  'categories.created_at as date_added',
                        'categories.updated_at as last_modified', 'categories_description.categories_name as name',
                        'categories_description.language_id','categoryTable.path as imgpath','iconTable.path as iconpath','categories.categories_status  as categories_status')
                        ->where('categories_description.language_id', '1')
                        ->where(function($query) {
                            $query->where('categoryTable.image_type', '=',  'THUMBNAIL')
                                ->where('categoryTable.image_type','!=',   'THUMBNAIL')
                                ->orWhere('categoryTable.image_type','=',   'ACTUAL')
                                ->where('iconTable.image_type', '=',  'THUMBNAIL')
                                ->where('iconTable.image_type','!=',   'THUMBNAIL')
                                ->orWhere('iconTable.image_type','=',   'ACTUAL');
                        })
                        ->where('categories_description.categories_name', 'like', "%".$param."%")
                    ->where('categories.parent_id', $categories->categories_id)
                        ->groupby('categories.categories_id')
                        ->paginate(1000);
                     foreach ($categoriesnew as $key=>$categoryq){
                        $url = url('category/'.$request->slug.'/'.$categoryq->categories_slug);
                        $html.="<li class='col-md-6'><a href=$url>$categoryq->name</a></li>";
                    }
                    }else{
                        foreach($SubCategories as $list) {
                            $CatName = DB::table('categories_description')
                            ->where('categories_id', $list->categories_id)
                            ->first();
                           $url = url('category/'.$request->slug.'/'.$list->categories_slug);
                            $html.="<li class='col-md-6'><a href=$url>$CatName->categories_name</a></li>";
                        //     // $html.='<div class="mt-5"><h1>wwww</h1><p>wwwww</p></div>';
                        }
                    }
                return $html;
            }
            if(!empty($categories->categories_slug)){
                Session::put('parent_pro_category_slug', $categories->categories_slug);
                Session::put('parent_pro_category_name', $CategoriesTitle->categories_name);
            }


            return view("web.details.product-category", ['title' => $title, 'SubCategories' => $SubCategories, 'categories' => $categories, 'CategoriesTitle' => $CategoriesTitle, 'slug' => $request->slug,'searchdata' => $searchdata])->with('result', $result);
        // }
    }

    public function productSubCategory(Request $request)
    {

        if(isset($request->slug) && !empty($request->slug)){
            $SubCategories = '';
            $categories = DB::table('categories')
                    ->where('categories_slug', $request->slug)
                    ->first();
            if($categories){
                $SubCategories = DB::table('categories')
                    ->where('parent_id', $categories->categories_id)
                    ->get();
            }
            $CategoriesTitle = DB::table('categories_description')
                    ->where('categories_id',$categories->categories_id)
                    ->first();
            $CategoriesSlug = DB::table('categories')
                    ->where('categories_id',$categories->categories_id)
                    ->first();
            $title = array('pageTitle' => Lang::get("website.View Cart"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();

            if(isset($request->search) && !empty($request->search)){
                $data = array('page_number' => '', 'type' => '', 'limit' => '9999999',
                'categories_id' => $categories->categories_id, 'search' => $request->search,
                'filters' => '', 'limit' => '9999999', 'min_price' => '0', 'max_price' => '9999999','brand' => '');
            }else{
                $data = array('page_number' => '', 'type' => '', 'limit' => '9999999',
            'categories_id' => $categories->categories_id, 'search' => '',
            'filters' => '', 'limit' => '9999999', 'min_price' => '0', 'max_price' => '9999999','brand' => '');
            }
            $products = $this->products->products($data);
            $result['products'] = $products;
            Session::put('products_category', $CategoriesSlug->categories_slug);
            Session::put('products_category_name', $CategoriesTitle->categories_name);

            return view("web.details.sub-category", ['title' => $title, 'SubCategories' => $SubCategories, 'categories' => $categories, 'CategoriesTitle' => $CategoriesTitle,'products' => $products,'CategoriesSlug'=> $CategoriesSlug])->with('result', $result);
        }
    }
    public function GetProId(Request $request){
        if(request()->ajax()){
            if(isset($request->id) && !empty($request->id)){
                $data = array('page_number' => '', 'type' => '', 'limit' => '9999999',
                'products_id ' => $request->id, 'search' => '',
                'filters' => '', 'limit' => '9999999', 'min_price' => '0', 'max_price' => '9999999','brand' => '');
                $products = $this->products->getProductsById($request->id);
                $productsTable = DB::table('products')->where('products_id', $request->id)->first();
                $productsName = DB::table('products_description')->where('products_id', $request->id)->first();
                $productsImage = DB::table('image_categories')->where('image_id', $productsTable->products_image)->first();

                $productsAttributes = DB::table('products_attributes')->where('products_id', $request->id)->where('options_values_price','!=', '')->get();
                $sectotal = array();
                if(!empty($productsAttributes)){
                    foreach($productsAttributes as $attributPrice){
                        $sectotal[] = $attributPrice->options_values_price;
                    }
                }
               // dd($productsAttributes);
                $data = '<figure><a href="'.url('product-detail/'.$productsTable->products_slug).'">';
                $data .= '<img src="'.url($productsImage->path).'" alt=""></a><figcaption>';
                $data .= '<h4 class="wow jello"><a href="'.url('product-detail/'.$productsTable->products_slug).'">'.$productsName->products_name.'</a></h4>';
                $data .='<h3> $'.min($sectotal). '- $'.max($sectotal).'</h3><a class="btn btn-default wow fadeIn" href="'.url('product-detail/'.$productsTable->products_slug).'">View More</a>';
                $data .= '</figcaption></figure>';
                echo  $data;

                // print_r($products);
        }
        }
    }

    // All Pages
    public function Disclaimer(Request $request){
        $title = array('pageTitle' => Lang::get("website.Disclaimer"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.disclaimer",['title' => $title,])->with('result', $result);
    }

    public function AboutFinefragrance(Request $request){
        $title = array('pageTitle' => Lang::get("website.About Fine Fragrance Oil"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.about-fine-oil",['title' => $title,])->with('result', $result);
    }

    public function StoringWearingFragrance(Request $request){
        $title = array('pageTitle' => Lang::get("website.Storing And Wearing Fragrance"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.storing-and-wearing-fragrance",['title' => $title, ])->with('result', $result);
    }

    public function AboutUs(Request $request){
        $title = array('pageTitle' => Lang::get("About Us"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.about-us",['title' => $title,])->with('result', $result);
    }

    public function SafeAndSecure(Request $request){
        $title = array('pageTitle' => Lang::get("Naja Safe and Secure"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.naja-safe-and-secure",['title' => $title, ])->with('result', $result);
    }

    public function PrivacyPolicy(Request $request){
        $title = array('pageTitle' => Lang::get("Privacy Policy"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
            $page = DB::table('pages_description')
            ->where('page_id', '10')
            ->first();
        return view("web.page.privacy-policy",['title' => $title, ])->with('result', $result)->with(['page' => $page]);
    }
    public function MembershipTerms(Request $request){
        $title = array('pageTitle' => Lang::get(" Membership T&C"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
			$result['milestone_details'] = DB::table('milestone')->get();
            // $final_theme = $this->theme->theme();
            $page = DB::table('pages_description')
            ->where('page_id', '12')
            ->first();
        return view("web.page.membership",['title' => $title, ])->with('result', $result)->with(['page' => $page]);
    }

    public function TermsOfUse(Request $request){
        $title = array('pageTitle' => Lang::get("website.Terms of Use"));
            $result = array();
            $data = array();
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
            $page = DB::table('pages_description')
            ->where('page_id', '11')
            ->first();
        return view("web.page.term-of-use",['title' => $title,])->with('result', $result)->with(['page' => $page]);
    }

    //ZST Add for FAQ Start !-->
    public function Faq(Request $request){
            $title = array('pageTitle' => Lang::get("website.FAQ"));
            $result = array();
            $data = array();
            $result['faq_questions'] = Footerlink::get_faq($request);
            // dd($result);
            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            // $final_theme = $this->theme->theme();
        return view("web.page.faq",['title' => $title, ])->with('result', $result);
    }
     //ZST Add for FAQ End !-->

    public function search(Request $request)
    {
        $title = array('pageTitle' => Lang::get('website.Shop'));
        $result = array();
        $result['fetch_footer_link'] = $this->footerlink->footer_link();
        $result['commonContent'] = $this->index->commonContent();
        // $final_theme = $this->theme->theme();
        $SubCategory = array();
        if(isset($request->search) && !empty($request->search)){
        $param = $request->search;
        $SubCategory = Categories::sortable(['categories_id'=>'ASC'])
        ->leftJoin('categories_description','categories_description.categories_id', '=', 'categories.categories_id')
            ->leftJoin('images','images.id', '=', 'categories.categories_image')
            ->leftJoin('image_categories as categoryTable','categoryTable.image_id', '=', 'categories.categories_image')
            ->leftJoin('image_categories as iconTable','iconTable.image_id', '=', 'categories.categories_icon')
            ->select('categories.categories_id as id', 'categories.parent_id as parent_id', 'categories.categories_image as image',
            'categories.categories_slug as categories_slug',
            'categories_description.categories_name as name',
            'categories_description.language_id','categories.categories_status  as categories_status')
            ->where('categories_description.language_id', '1')
            ->where('categories_description.categories_name', 'like', "%".$param ."%")
            ->groupby('categories.categories_id')
            ->paginate(999999999);
        }
        return view("web.search", ['title' => $title, 'SubCategory' => $SubCategory, 'searchname' => $request->search])->with('result', $result);

        // return view("web.search",['title' => $title, 'final_theme' => $final_theme])->with('result', $result);
    }


}
