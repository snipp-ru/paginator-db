class DBPaginator
{
	public  $page    = 1;   /* Текущая страница */
	public  $amt     = 0;   /* Кол-во страниц */
	public  $limit   = 10;  /* Кол-во элементов на странице */
	public  $total   = 0;   /* Общее кол-во элементов */
	public  $display = '';	/* HTML-код навигации */
 
	private $url     = '';       
	private $carrier = 'page';
 
	/**
	 * Конструктор.
	 */
	public function __construct($url, $limit = 0)
	{
		$this->url = $url;		
		
		if (!empty($limit)) {
			$this->limit = $limit;
		}		
 
		$page = intval(@$_GET['page']);
		if (!empty($page)) {
			$this->page = $page;
		}
 
		$query = parse_url($this->url, PHP_URL_QUERY);
		if (empty($query)) {
			$this->carrier = '?' . $this->carrier . '=';
		} else {
			$this->carrier = '&' . $this->carrier . '=';
		}
	}
 
	/**
	 * Формирование HTML-кода навигации в переменную display.
	 */
	public function getItems($sql)
	{
		// Подключение к БД
		$dbh = new PDO('mysql:dbname=db_name;host=localhost', 'логин', 'пароль');
 
		// Получение записей для текущей страницы
		$start = ($this->page != 1) ? $this->page * $this->limit - $this->limit : 0;
		if (strstr($sql, 'SQL_CALC_FOUND_ROWS') === false) {
			$sql = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $sql) . ' LIMIT ' . $start . ', ' . $this->limit;
		} else {
			$sql = $sql . ' LIMIT ' . $start . ', ' . $this->limit;
		}		
		
		$sth = $dbh->prepare($sql);
		$sth->execute();		
		$array = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		// Узнаем сколько всего записей в БД 
		$sth = $dbh->prepare("SELECT FOUND_ROWS()");
		$sth->execute();
		$this->total = $sth->fetch(PDO::FETCH_COLUMN);
		
		$this->amt = ceil($this->total / $this->limit);	
		if ($this->page > $this->amt) {
			$this->page = $this->amt;
		}
 
		if ($this->amt > 1) {
			$adj = 2;	
			$this->display = '<nav class="pagination-row"><ul class="pagination justify-content-center">';
 
			/* Назад */
			if ($this->page == 1) {
				$this->addSpan('«', 'prev disabled');
			} elseif ($this->page == 2) {
				$this->addLink('«', '', 'prev');
			} else {
				$this->addLink('«', $this->carrier . ($this->page - 1), 'prev');
			}
 
			if ($this->amt < 7 + ($adj * 2)) {
				for ($i = 1; $i <= $this->amt; $i++){
					$this->addLink($i, $this->carrier . $i);			
				}
			} elseif ($this->amt > 5 + ($adj * 2)) {
				$lpm = $this->amt - 1;
				if ($this->page < 1 + ($adj * 2)){
					for ($i = 1; $i < 4 + ($adj * 2); $i++){
						$this->addLink($i, $this->carrier . $i);
					}
					$this->addSpan('...', 'separator');	
					$this->addLink($lpm, $this->carrier . $lpm);
					$this->addLink($this->amt, $this->carrier . $this->amt);	
				} elseif ($this->amt - ($adj * 2) > $this->page && $this->page > ($adj * 2)) {
					$this->addLink(1);
					$this->addLink(2, $this->carrier . '2');
					$this->addSpan('...', 'separator');	
					for ($i = $this->page - $adj; $i <= $this->page + $adj; $i++) {
						$this->addLink($i, $this->carrier . $i);
					}
					$this->addSpan('...', 'separator');	
					$this->addLink($lpm, $this->carrier . $lpm);
					$this->addLink($this->amt, $this->carrier . $this->amt);	
				} else {
					$this->addLink(1, '');
					$this->addLink(2, $this->carrier . '2');
					$this->addSpan('...', 'separator');	
					for ($i = $this->amt - (2 + ($adj * 2)); $i <= $this->amt; $i++) {
						$this->addLink($i, $this->carrier . $i);
					}
				}
			}
 
			/* Далее */
			if ($this->page == $this->amt) {
				$this->addSpan('»', 'next disabled');				
			} else {			
				$this->addLink('»', $this->carrier . ($this->page + 1));
			}
 
			$this->display .= '</ul></nav>';
		}
 
		return $array;	
	}
 
	private function addSpan($text, $class = '')
	{
		$class = 'page-item ' . $class;
		$this->display .= '<li class="' . trim($class) . '"><span class="page-link">' . $text . '</span></li>';		
	}	
 
	private function addLink($text, $url = '', $class = '')
	{
		if ($text == 1) {
			$url = '';
		}
 
		$class = 'page-item ' . $class . ' ';
		if ($text == $this->page) {
			$class .= 'active';
		}
		$this->display .= '<li class="' . trim($class) . '"><a class="page-link" href="' . $this->url . $url . '">' . $text . '</a></li>';
	}	
}
