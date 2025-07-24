<?php
include "db.php";

$previewImage = "";
$result = "";

if (isset($_POST['upload'])) {

    $apiURL = "https://ucwgwgkko4wk408ggsk0cosw.oiio.download/api/slip";

    $imgTmp = $_FILES['slipImage']['tmp_name'];
    $imgType = mime_content_type($imgTmp);
    $fileContent = file_get_contents($imgTmp);
    $base64Image = 'data:' . $imgType . ';base64,' . base64_encode($fileContent);

    // echo $base64Image;

    $postData = json_encode([
        'img' => $base64Image
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // echo $response;

    $res = json_decode($response, true);

    if (isset($res['error'])) {
        $result = '<div class="alert alert-danger mb-2">' . $res['error'] . '</div>';
    } else {

        $ref = $res['data']['ref'];
        $tranfer_date = $res['data']['date'];
        $senderBank = $res['data']['sender_bank'];
        $senderName = $res['data']['sender_name'];
        // $receiverBank = $res['data']['receiver_bank'];
        $receiverName = $res['data']['receiver_name'];
        $amount = $res['data']['amount'];

        $sqlCheckRef  = "SELECT ref FROM slip_log WHERE ref = '" . $res['data']['ref'] . "'";
        $resultCheckRef = mysqli_query($conn, $sqlCheckRef);
        if (mysqli_num_rows($resultCheckRef) > 0) {
            $result = '<div class="alert alert-danger mb-2">' . "มี Slip นี้อยู่ในระบบแล้ว" . '</div>';
        } else {
            if ($res['data']['receiver_name'] !== "KANNIKA CHERD") {
                $result = '<div class="alert alert-danger mb-2">' . "ชื่อบัญชีไม่ตรงกัน" . '</div>';
            } else {
                $sql = "INSERT INTO slip_log (ref, sender_name, sender_bank, receiver_name, amount, tranfer_date)
        VALUES ('$ref','$senderName','$senderBank','$receiverName','$amount','$tranfer_date')";
                $query = mysqli_query($conn, $sql);
                if ($query) {
                    $result = '<div class="alert alert-success mb-2">' . "ทำรายการสำเร็จจำนวน " . $res['data']['amount'] . " บาท" . '</div>';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Test Slip</title>
</head>

<body>
    <style>
        @font-face {
            font-family: "Samsung";
            src: url("/font/SamsungOneThai-600.ttf");
        }

        * {
            font-family: "Samsung";
        }
    </style>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 bg-white shadow-lg p-5">
                <div id="result"><?= $result ?></div>
                <h1 class="font-bold">ระบบตรวจสอบ Slip v0.1</h1>
                <form method="post" enctype="multipart/form-data">
                    <label class="form-label" for="">อัพโหลดสลิป</label>
                    <input class="form-control" type="file" name="slipImage" accept="image/*" onchange="previewImage(event)" required>
                    <img class="w-50" id="imagePreview" src="<?= $previewImage ?>">
                    <button name="upload" type="submit" class="btn btn-primary mt-2 w-100">ยืนยัน</button>
                </form>

            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const input = event.target;
            const reader = new FileReader();
            reader.onload = function() {
                const img = document.getElementById('imagePreview');
                img.src = reader.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>

</body>

</html>