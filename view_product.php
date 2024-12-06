<?php
// Fetch product and inventory data
$products = $conn->query("SELECT * FROM products WHERE md5(id) = '{$_GET['id']}' ");
if ($products->num_rows > 0) {
    $product = $products->fetch_assoc();
    $upload_path = base_app . '/uploads/product_' . $product['id'];
    $img = "";
    if (is_dir($upload_path)) {
        $fileO = array_diff(scandir($upload_path), array('.', '..', 'None'));
        if (isset($fileO[2]))
            $img = "uploads/product_" . $product['id'] . "/" . $fileO[2];
    }
    $inventory = $conn->query("SELECT * FROM inventory WHERE product_id = " . $product['id']);
    $inv = [];
    while ($ir = $inventory->fetch_assoc()) {
        $inv[] = $ir;
    }

    // Fetching product features for collaborative filtering
    $product_features = $conn->query("SELECT * FROM products WHERE id = " . $product['id']);
    $features = $product_features->fetch_assoc();
    $features_vector = [
        'category_id' => $features['category_id'],
        'sub_category_id' => $features['sub_category_id'],
        // Add more features as needed
    ];

    // Compute similarity scores
    function calculate_similarity($features1, $features2) {
        $similarity = 0;
        if ($features1['category_id'] == $features2['category_id']) $similarity += 1;
        if ($features1['sub_category_id'] == $features2['sub_category_id']) $similarity += 1;
        // Add more conditions based on features
        return $similarity;
    }

    $all_products = $conn->query("SELECT * FROM products WHERE id != " . $product['id']);
    $similar_products = [];

    while ($row = $all_products->fetch_assoc()) {
        $other_features = [
            'category_id' => $row['category_id'],
            'sub_category_id' => $row['sub_category_id'],
            // Add more features as needed
        ];
        $similarity = calculate_similarity($features_vector, $other_features);
        if ($similarity > 0) {
            $similar_products[] = ['product' => $row, 'similarity' => $similarity];
        }
    }

    // Sort similar products by similarity score
    usort($similar_products, function($a, $b) {
        return $b['similarity'] - $a['similarity'];
    });

    // Fetch top 4 similar products
    $top_similar_products = array_slice($similar_products, 0, 4);
}
?>
<section class="py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="row gx-4 gx-lg-5 align-items-center">
            <div class="col-md-6">
                <img class="card-img-top mb-5 mb-md-0" loading="lazy" id="display-img" src="<?php echo validate_image($img) ?>" alt="..." />
                <div class="mt-2 row gx-2 gx-lg-3 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-start">
                    <?php 
                        foreach($fileO as $k => $img):
                            if(in_array($img, array('.', '..')))
                                continue;
                    ?>
                        <a href="javascript:void(0)" class="view-image <?php echo $k == 2 ? 'active' : '' ?>">
                            <img src="<?php echo validate_image('uploads/product_'.$product['id'].'/'.$img) ?>" loading="lazy" class="img-thumbnail" alt="">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="display-5 fw-bolder"><?php echo htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="fs-5 mb-5">
                    <span><b>Rs <?php echo number_format($inv[0]['price']) ?></span><br>
                    <span><small><b>Available stock:</b> <span id="avail"><?php echo $inv[0]['quantity'] ?></span></small></span>
                </div>
                <form action="" id="add-cart">
                    <div class="d-flex">
                        <input type="hidden" name="price" value="<?php echo $inv[0]['price'] ?>">
                        <input type="hidden" name="inventory_id" value="<?php echo $inv[0]['id'] ?>">
                        <input class="form-control text-center me-3" id="inputQuantity" type="number" value="1" style="max-width: 3rem" name="quantity" />
                        <button class="btn btn-outline-dark flex-shrink-0" type="submit">
                            <i class="bi-cart-fill me-1"></i>
                            Add to cart
                        </button>
                    </div>
                </form>
                <p class="lead"><?php echo stripslashes(html_entity_decode($product['description'])) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Related items section-->
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5 mt-5">
        <h2 class="fw-bolder mb-4">Related products</h2>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        <?php 
            foreach ($top_similar_products as $sim_product):
                $row = $sim_product['product'];
                $upload_path = base_app.'/uploads/product_'.$row['id'];
                $img = "";
                if(is_dir($upload_path)){
                    $fileO = array_diff(scandir($upload_path), array('.', '..', 'None'));
                    if(isset($fileO[2]))
                        $img = "uploads/product_".$row['id']."/".$fileO[2];
                }
                $inventory = $conn->query("SELECT * FROM inventory WHERE product_id = ".$row['id']);
                $_inv = array();
                while($ir = $inventory->fetch_assoc()){
                    if (!empty($ir['price'])) { // Only add inventory with price
                        $_inv[$ir['size']] = number_format($ir['price']);
                    }
                }
                if (empty($_inv)) {
                    continue; // Skip if no valid price entries
                }
        ?>
            <div class="col mb-5">
                <div class="card h-100 product-item">
                    <img class="card-img-top w-100" src="<?php echo validate_image($img) ?>" alt="..." />
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h5 class="fw-bolder"><?php echo htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <?php foreach($_inv as $k => $v): ?>
                                <span> Rs: <b><?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?></b> <?php echo $v ?></span><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center">
                            <a class="btn btn-flat btn-primary" href=".?p=view_product&id=<?php echo md5($row['id']) ?>">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    $(function(){
        $('.view-image').click(function(){
            var _img = $(this).find('img').attr('src');
            $('#display-img').attr('src', _img);
            $('.view-image').removeClass("active")
            $(this).addClass("active")
        });

        $('#add-cart').submit(function(e){
            e.preventDefault();
            if('<?php echo $_settings->userdata('id') ?>' <= 0){
                uni_modal("","login.php");
                return false;
            }
            start_loader();
            $.ajax({
                url: 'classes/Master.php?f=add_to_cart',
                data: $(this).serialize(),
                method: 'POST',
                dataType: "json",
                success: function(resp){
                    if(typeof resp == 'object' && resp.status == 'success'){
                        alert_toast("Product added to cart.",'success');
                        $('#cart-count').text(resp.cart_count);
                    } else {
                        console.log(resp);
                        alert_toast("An error occurred",'error');
                    }
                    end_loader();
                },
                error: function(err){
                    console.log(err);
                    alert_toast("An error occurred",'error');
                    end_loader();
                }
            });
        });
    });
</script>
