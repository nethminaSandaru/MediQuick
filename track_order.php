<?php

session_start();

require_once "config/db.php";

$orders = [];

if (isset($_SESSION['user_id'])) {

    $stmt = $conn->prepare(
        "SELECT *
         FROM orders
         WHERE user_id = ?
         ORDER BY order_id DESC"
    );

    $stmt->bind_param(
        "i",
        $_SESSION['user_id']
    );

    $stmt->execute();

    $orders =
        $stmt->get_result();
}

?>


<?php include "includes/header.php"; ?>


<main class="section">

    <div class="container">


        <div class="section-title">

            <h2>
                Track Your Orders
            </h2>

            <p>
                View your latest orders and delivery status.
            </p>

        </div>


        <?php if (!isset($_SESSION['user_id'])): ?>


            <div class="card center">

                <h3>
                    Login Required
                </h3>

                <p>
                    Please login to view your orders.
                </p>

                <br>

                <a
                    class="btn btn-primary"
                    href="login.php"
                >
                    Login
                </a>

            </div>


        <?php elseif ($orders->num_rows > 0): ?>


            <div class="table-wrap">

                <table class="table">

                    <thead>

                        <tr>

                            <th>
                                Order
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Payment
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $order =
                        $orders->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>
                                <strong>
                                    #<?= (int)$order['order_id'] ?>
                                </strong>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $order['created_at']
                                ) ?>
                            </td>


                            <td>

                                LKR
                                <?= number_format(
                                    $order['total_amount'],
                                    2
                                ) ?>

                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $order['payment_method']
                                ) ?>
                            </td>


                            <td>

                                <span class="badge">

                                    <?= htmlspecialchars(
                                        $order['status']
                                    ) ?>

                                </span>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="card center">

                <h3>
                    No Orders Yet
                </h3>

                <p>
                    You haven't placed any orders yet.
                </p>

                <br>

                <a
                    class="btn btn-primary"
                    href="products.php"
                >
                    Browse Medicines
                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


<?php include "includes/footer.php"; ?>