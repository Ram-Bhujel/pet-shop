<script src="https://www.khalti.com/api/checkout.js"></script>
<?php 

$total = 0;

$qry = $conn->query("SELECT c.*,p.product_name,i.size,i.price,p.id as pid from `cart` c inner join `inventory` i on i.id=c.inventory_id inner join products p on p.id = i.product_id where c.client_id = ".$_settings->userdata('id'));
while($row= $qry->fetch_assoc()):
    $total += $row['price'] * $row['quantity'];
endwhile;
?>
<section class="py-5">
    <div class="container">
        <div class="card rounded-0">
            <div class="card-body"></div>
            <h3 class="text-center"><b>Checkout</b></h3>
            <hr class="border-dark">
            
                <input type="hidden" name="amount" value="<?php echo $total ?>">
                <input type="hidden" name="payment_method" value="cod">
                <input type="hidden" name="paid" value="0">
                <div class="row row-col-1 justify-content-center">
                    <div class="col-6">
                        <div class="form-group col">
                            <div class="col">
                                <span><h4><b>Total:</b> <?php echo number_format($total) ?></h4></span>
                            </div>
                            <hr>
                            <div class="col my-3">
                                <h4 class="text-muted">Payment Method</h4>
                              

                                <?php   
                                        $getUser = $conn->query("SELECT * FROM clients Where id=".$_settings->userdata('id'));
                                        $user = mysqli_fetch_assoc($getUser);
                                    // var_dump($user);

                                ?>

                                <form action="http://localhost/PET_SHOP/khalti-payment/checkout.php" method="get">
                                    <input type="hidden" name="amount" value= <?php echo number_format($total) ?>>
                                    <input type="hidden" name="p_id" value= <?php echo uniqid(); ?>>
                                    <input type="hidden" name="order-name" value="<?php echo $user['firstname']." ".$user['lastname']."'s order for ".date("y-m-d h:m:i"); ?>">
                                    <input type="hidden" name="user-name" value="<?php echo $user['firstname']." ".$user['lastname']; ?>">
                                    <input type="hidden" name="user-email" value="<?php echo $user['email']; ?>">
                                    <input type="hidden" name="user-phone" value="<?php echo $user['contact']; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                    <button type="submit" id="khalti_payment_btn" class="btn btn-primary">Pay with Khalti</button>
                                </div>
                                </form>
                                test

 

                            <?php echo    var_dump($_settings->userdata('id'))?>
                            </div>
                        </div>
                    </div>
                </div>
            
        </div>
    </div>
</section>

<script>
    console.log("hh");
    let uid = <?php echo $_settings->userdata('id')?>;
    console.log("uid "+uid);


    document.getElementById("khalti_test").addEventListener("click",()=>{
        window.location.href = "../PET_SHOP/khalti-ePayment-gateway-main/TestPay.php";
    })

$(function(){
    // When user clicks the "Pay with Khalti" button, redirect to the Khalti payment page
    $('#khalti_payment_btn').click(function(e){
        e.preventDefault();
        // Redirect to the Khalti payment page with the order details
        window.location.href = '../PET_SHOP/khalti-payment/checkout.php?amount=' + $('[name="amount"]').val()+"&&user_id="+uid;
    });

  
});
</script>
