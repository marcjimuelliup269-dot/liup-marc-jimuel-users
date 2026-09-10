<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller
{
    private $products;

    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->products = $this->call->model('ProductModel');
    }

    public function index()
    {
        $products = $this->products->order_by('id', 'DESC');
        $this->call->view('products', ['products' => $products]);
    }

    public function create()
    {
        $this->call->view('product_form', [
            'product' => [],
            'form_action' => '/products',
            'form_title' => 'Add product',
            'error' => null,
        ]);
    }

    public function store()
    {
        $data = $this->validated_input();
        if (isset($data['error'])) {
            $this->call->view('product_form', array_merge($data, [
                'product' => $_POST,
                'form_action' => '/products',
                'form_title' => 'Add product',
            ]));
            return;
        }

        $this->products->insert($data);
        redirect('/products');
    }

    public function edit($id)
    {
        $product = $this->products->find((int)$id);
        if (!$product) {
            show_404();
        }

        $this->call->view('product_form', [
            'product' => $product,
            'form_action' => '/products/' . (int)$id,
            'form_title' => 'Edit product',
            'error' => null,
        ]);
    }

    public function update($id)
    {
        $data = $this->validated_input();
        if (isset($data['error'])) {
            $data['product'] = array_merge($_POST, ['id' => (int)$id]);
            $data['form_action'] = '/products/' . (int)$id;
            $data['form_title'] = 'Edit product';
            $this->call->view('product_form', $data);
            return;
        }

        $this->products->update((int)$id, $data);
        redirect('/products');
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->products->delete((int)$id);
        }

        redirect('/products');
    }

    private function validated_input()
    {
        $product_name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');

        if ($product_name === '' || strlen($product_name) > 100) {
            return ['error' => 'Product name is required and must be 100 characters or fewer.'];
        }
        if (!is_numeric($price) || (float)$price < 0) {
            return ['error' => 'Price must be a non-negative number.'];
        }
        if (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int)$quantity < 0) {
            return ['error' => 'Quantity must be a non-negative whole number.'];
        }

        return [
            'product_name' => $product_name,
            'description' => $description,
            'price' => number_format((float)$price, 2, '.', ''),
            'quantity' => (int)$quantity,
        ];
    }
}
