<?php if (!$arCityes = include $_SERVER['DOCUMENT_ROOT'] . '/.cityes.php') {
    return false;
} ?>

<div class="mfeedback-p" id="location" style="display: none; max-width: 500px;">
    <span class="button b-close"><span>&times;</span></span>
    <div class="mfeedback-p-head">Выбор города</div>

    <?php if ($arCityes['show'] && $arCityes['hide']): ?>
    <div class="city">
        <?php if ($arCityes['show']): ?>
        <div class="item-city">
            <?php foreach ($arCityes['show'] as $s): ?>
            <a href="javascript:void(0)" class="c"><?= $s ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="item-city">
            <a href="javascript:void(0)" class="city-show" onclick="$(this).closest('.city').find('.item-city:last-child').toggleClass('active'); return false;">Показать все города</a>
        </div>

        <?php if ($arCityes['hide']): ?>
        <div class="item-city">
            <?php foreach ($arCityes['hide'] as $h): ?>
            <a href="javascript:void(0)" class="c"><?= $h ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
    .city .item-city {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin: 10px auto;
        padding: 0 10px;
    }
    .city .item-city a {
        width: 140px;
        margin-bottom: 5px;
        color: #575b71;
        text-decoration: none;
    }
    .city .item-city a:hover {
        color: #c82c30;
    }
    .city .item-city a.city-show {
        flex-grow: 1;
        text-align: center;
    }
    .city .item-city:last-child {
        display: none;
    }
    .city .item-city:last-child.active {
        display: flex;
    }
</style>
