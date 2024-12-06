<section class="py-2">
    <div class="container">
        <div class="card rounded-0">
            <div class="card-body">
                <div class="w-100 justify-content-between d-flex">
                    <h4><b>Orders</b></h4>
                    <a href="./?p=edit_account" class="btn btn-dark btn-flat"><i class="fa fa-user-cog"></i> Manage Account</a> <!-- Fixed button structure and removed extra "btn" class -->
                </div>
                <hr class="border-warning">
                <table class="table table-striped text-dark"> <!-- Fixed table class typo: 'stripped' to 'striped' -->
                    <colgroup>
                        <col width="10%">
                        <col width="15%">
                        <col width="25%">
                        <col width="25%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date/Time</th> <!-- Updated heading for clarity -->
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $i = 1;
                            $qry = $conn->query("SELECT o.*, CONCAT(c.firstname, ' ', c.lastname) as client FROM `orders` o 
                                                 INNER JOIN clients c ON c.id = o.client_id 
                                                 WHERE o.client_id = '".$_settings->userdata('id')."' 
                                                 ORDER BY UNIX_TIMESTAMP(o.date_created) DESC");
                            while($row = $qry->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo date("Y-m-d H:i", strtotime($row['date_created'])); ?></td>
                                <td><a href="javascript:void(0)" class="view_order" data-id="<?php echo $row['id']; ?>"><?php echo md5($row['id']); ?></a></td>
                                <td><?php echo number_format($row['amount'], 2); ?></td>
                                <td class="text-center">
                                    <?php 
                                        switch($row['status']) {
                                            case 0:
                                                echo '<span class="badge badge-light text-dark">Paid</span>';
                                                break;
                                            case 1:
                                                echo '<span class="badge badge-primary">Unpaid</span>';
                                                break;
                                            case 2:
                                                echo '<span class="badge badge-warning">Pending</span>';
                                                break;
                                            
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
    function cancel_order(id) { // Updated function name for clarity
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=update_order_status", // Updated function name in URL
            method: "POST",
            data: { id: id, status: 2 },
            dataType: "json",
            error: function(err) {
                console.log(err);
                alert_toast("An error occurred", 'error'); // Corrected the spelling
                end_loader();
            },
            success: function(resp) {
                if (typeof resp === 'object' && resp.status === 'success') {
                    alert_toast("Order cancelled successfully", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    console.log(resp);
                    alert_toast("An error occurred", 'error');
                }
                end_loader();
            }
        });
    }

    $(function() {
        $('.view_order').click(function() {
            uni_modal("Order Details", "./admin/orders/view_order.php?view=user&id=" + $(this).attr('data-id'), 'large');
        });
        $('table').DataTable(); // Fixed dataTable initialization
    });
</script>
