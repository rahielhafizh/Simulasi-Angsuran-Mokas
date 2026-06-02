<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Mobil Bekas</title>
    <?php include __DIR__ . '/styles.php'; ?>
</head>

<body>
    <?php
    $areaNewRaw = $_SESSION['area_new'] ?? null;
    $areaNew = strtoupper(trim($areaNewRaw ?? ''));
    $nonJawaAreas = ['KALIMANTAN', 'IBT', 'SULAWESI', 'SUMBAGSEL', 'SUMBAGUT&TENG'];
    $minDpPercentLabel = in_array($areaNew, $nonJawaAreas, true) ? '25%' : '20%';
    ?>

    <div class="container">
        <?php include __DIR__ . '/header.php'; ?>
        <?php include __DIR__ . '/search.php'; ?>
        <?php include __DIR__ . '/alert.php'; ?>
        <?php include __DIR__ . '/form_kriteria.php'; ?>
        <?php include __DIR__ . '/form_detail.php'; ?>
        <?php include __DIR__ . '/result_main.php'; ?>
        <?php include __DIR__ . '/modal_mrp.php'; ?>
    </div>

    <?php include __DIR__ . '/scripts.php'; ?>
</body>

</html>