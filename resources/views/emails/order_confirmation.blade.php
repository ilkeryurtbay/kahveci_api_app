<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Onayı</title>
</head>
<body>
<p>Sayın {{ $user->name }},</p>
<p>Siparişiniz başarıyla alındı. Toplam tutar: {{ $totalAmount }} TL.</p>
<p>İyi günler dileriz.</p>
</body>
</html>
