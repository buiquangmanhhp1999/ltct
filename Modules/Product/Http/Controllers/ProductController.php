<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductImage;
use Modules\Product\Models\Tag;
use Modules\Product\Models\ProductTag;
use Modules\Category\Models\Category;
use Modules\Product\Components\Recursive;
use Modules\Product\Traits\StorageImageTrait;
use Modules\Product\Traits\DeleteModelTrait;

class ProductController extends Controller
{
    use StorageImageTrait, DeleteModelTrait;
    private $category;
    private $product;
    private $productImage;
    private $tag;
    private $productTag;
    public function __construct(Category $category, Product $product, ProductImage $productImage, Tag $tag, ProductTag $productTag)
    {
        $this->category = $category;
        $this->product = $product;
        $this->productImage = $productImage;
        $this->tag = $tag;
        $this->productTag = $productTag;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('product::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $htmlOption = $this->getCategory($parentId = '');
        return view('product::add', compact('htmlOption'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $dataProductCreate = [
            'name' => $request->name,
            'price' => $request->price,
            'content' => $request->contents,
            'category_id' => $request->category_id
        ];
        $dataUploadFeatureImage = $this->storageTraitUpload($request, 'feature_image_path', 'product');

        if(!empty($dataUploadFeatureImage)) {
            $dataProductCreate['feature_image_name'] = $dataUploadFeatureImage['file_name'];
            $dataProductCreate['feature_image_path'] = $dataUploadFeatureImage['file_path'];
        }
        $product = $this->product->create($dataProductCreate);

        // Insert data to product_images table
        if ($request->hasFile('image_path')) {
            foreach ($request->image_path as $fileItem) {
                $dataProductImageDetail = $this->storageTraitUploadMutiple($fileItem, 'product');
                $product->images()->create([
                    'image_path' => $dataProductImageDetail['file_path'],
                    'image_name' => $dataProductImageDetail['file_name']
                ]);
            }
        }

        // Insert data to product_tags table
        foreach($request->tags as $tagItem) {
            // Insert to tags
            $tagInstance = $this->tag->firstOrCreate(['name' => $tagItem]);
            $tagIds[] = $tagInstance->id;
        }
        $product->tags()->attach($tagIds);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('product::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('product::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete($id)
    {
        //
    }

    public function getCategory($parentId)
    {
        $data = $this->category->all();
        $recursive = new Recursive($data);
        $htmlOption = $recursive->categoryRecursive($parentId);
        return $htmlOption;
    }
}
