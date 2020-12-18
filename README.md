## Постраничный вывод и базы данных
Подробное описание на [Snipp.ru](https://snipp.ru/php/paginator-mysql).

#### Использование класса
```php
<?php
require_once('ArrayPaginator.php');

// Текущий URL и 6 шт. на странице
$peger = new DBPaginator('https://example.com', 6); 
$items = $peger->getItems("SELECT * FROM `prods`");
 
/* Инфо о текущей странице */
echo '<p>Страница ' . $peger->page . ' из ' . $peger->amt . '</p>';	
 
/* Вывод текста только на первой странице */
if ($peger->page == 1) {
	echo '<p>Описание на первой странице</p>';
}
?>
 
<div class="prod-list">
	<?php foreach ($items as $row): ?>
	<div class="prod-item">
		<div class="prod-item-img">
			<a href="#"><img src="/images/<?php echo $row['img']; ?>" alt=""></a>			
		</div>
		<div class="prod-item-name">
			<a href="#"><?php echo $row['name']; ?></a>			
		</div>
		<div class="prod-item-btn">
			<a href="#">Купить</a>
		</div>
	</div>
	<?php endforeach; ?>
</div>
 
<?php echo $peger->display; ?>
```
