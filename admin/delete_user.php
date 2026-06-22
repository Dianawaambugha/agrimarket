 diana 
 <?php

session_start();

require_once "../config/db.php";
require_once "../includes/log_activity.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

if(isset($_GET["id"]))
{
    $user_id = (int)$_GET["id"];

    if($user_id == $_SESSION["user_id"])
    {
        die("You cannot delete your own account.");
    }

    $check = $conn->prepare("
    SELECT *
    FROM users
    WHERE user_id=?
    ");

    $check->execute([$user_id]);

    $target = $check->fetch();

    if(!$target)
    {
        die("User not found.");
    }

    if($target["role"] == "Admin")
    {
        die("Admin accounts cannot be deleted.");
    }

    try
    {
        $conn->beginTransaction();

        /*
        Delete messages
        */

        $stmt = $conn->prepare("
        DELETE FROM messages
        WHERE sender_id=?
        OR receiver_id=?
        ");

        $stmt->execute([
            $user_id,
            $user_id
        ]);

        /*
        Buyer related records
        */

        $buyer = $conn->prepare("
        SELECT buyer_id
        FROM buyers
        WHERE user_id=?
        ");

        $buyer->execute([$user_id]);

        $buyerData = $buyer->fetch();

        if($buyerData)
        {
            $buyer_id = $buyerData["buyer_id"];

            $orders = $conn->prepare("
            SELECT order_id
            FROM orders
            WHERE buyer_id=?
            ");

            $orders->execute([$buyer_id]);

            $orderRows = $orders->fetchAll();

            foreach($orderRows as $order)
            {
                $tracking = $conn->prepare("
                DELETE FROM delivery_tracking
                WHERE order_id=?
                ");

                $tracking->execute([
                    $order["order_id"]
                ]);
            }

            $deleteOrders = $conn->prepare("
            DELETE FROM orders
            WHERE buyer_id=?
            ");

            $deleteOrders->execute([$buyer_id]);

            $deleteBuyer = $conn->prepare("
            DELETE FROM buyers
            WHERE buyer_id=?
            ");

            $deleteBuyer->execute([$buyer_id]);
        }

        /*
        Farmer related records
        */

        $farmer = $conn->prepare("
        SELECT farmer_id
        FROM farmers
        WHERE user_id=?
        ");

        $farmer->execute([$user_id]);

        $farmerData = $farmer->fetch();

        if($farmerData)
        {
            $farmer_id =
            $farmerData["farmer_id"];

            $products = $conn->prepare("
            SELECT product_id
            FROM products
            WHERE farmer_id=?
            ");

            $products->execute([$farmer_id]);

            $productRows =
            $products->fetchAll();

            foreach($productRows as $product)
            {
                $orders = $conn->prepare("
                SELECT order_id
                FROM orders
                WHERE product_id=?
                ");

                $orders->execute([
                    $product["product_id"]
                ]);

                $orderRows =
                $orders->fetchAll();

                foreach($orderRows as $order)
                {
                    $tracking = $conn->prepare("
                    DELETE FROM delivery_tracking
                    WHERE order_id=?
                    ");

                    $tracking->execute([
                        $order["order_id"]
                    ]);
                }

                $deleteOrders = $conn->prepare("
                DELETE FROM orders
                WHERE product_id=?
                ");

                $deleteOrders->execute([
                    $product["product_id"]
                ]);
            }

            $deleteProducts = $conn->prepare("
            DELETE FROM products
            WHERE farmer_id=?
            ");

            $deleteProducts->execute([
                $farmer_id
            ]);

            $deleteFarmer = $conn->prepare("
            DELETE FROM farmers
            WHERE farmer_id=?
            ");

            $deleteFarmer->execute([
                $farmer_id
            ]);
        }

        /*
        Audit Log
        */

        logActivity(
            $conn,
            $_SESSION["user_id"],
            "Deleted User: ".$target["full_name"]
        );

        /*
        Delete user
        */

        $deleteUser = $conn->prepare("
        DELETE FROM users
        WHERE user_id=?
        ");

        $deleteUser->execute([
            $user_id
        ]);

        $conn->commit();
    }
    catch(Exception $e)
    {
        $conn->rollBack();
        die($e->getMessage());
    }
}

header("Location: manage_users.php");
exit();

?>
