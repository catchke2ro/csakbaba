<?
/**
 * Navigation object for menus and breadcrumbs
 * Class SRG_Resource_Navigation
 * @author SRG Group
 */
class CB_Resource_Navigation extends Zend_Navigation {


	public function __construct(){
		$this->initNavigation();
		Zend_Registry::set('nav', $this);
	}

	public function getFirstParent($page){
		return (!$page->getParent() instanceof Zend_Navigation) ? $this->getFirstParent($page->getParent()) : $page;
	}



	function initNavigation(){
		$this->addPages(array(
			array('label'=>'Főoldal', 'title'=>'csakbaba.hu - Használt és új baba és gyerek ruházat, online bababörze', 'titleOverwrite'=>true, 'uri'=>'/', 'mvc'=>array('index','index'), 'resource'=>'fooldal'),
			array('label'=>'Börze', 'uri'=>'/borze', 'mvc'=>array('market','index'), 'resource'=>'piac'),
			array('label'=>'Kiemelt termékek', 'uri'=>'/kiemelt', 'mvc'=>array('market','promoted'), 'resource'=>'kiemelt', 'visible'=>false),
			array('label'=>'Asztalom', 'notValid'=>array('label'=>'Oldalam'), 'class'=>'user', 'uri'=>'/felhasznalo', 'mvc'=>array('user', 'index'), 'resource'=>'felhasznalo', 'noindex'=>true, 'visible'=>true, 'pages'=>array(
				array('label'=>'Adataim', 'uri'=>'/felhasznalo/adatmodositas', 'mvc'=>array('user','edit'), 'resource'=>'adatmodositas', 'visible'=>true),
				array('label'=>'Profil törlés', 'uri'=>'/profiltorles', 'mvc'=>array('user','profiledelete'), 'visible'=>false, 'profileVisible' => false, 'resource'=>'profiltorles'),
				array('label'=>'Termékeim, termékfeltöltés', 'notValid'=>array('label'=>'Asztalnyitás', 'url'=>'/felhasznalo/adatmodositas?nyitas=1'), 'uri'=>'/felhasznalo/termekek', 'mvc'=>array('shop','userproducts'), 'resource'=>'felhasznalotermekek', 'visible'=>true),
				array('label'=>'Vásárlásaim, értékelés', 'uri'=>'/felhasznalo/rendeleseim', 'mvc'=>array('user','orders'), 'resource'=>'ertekelesek', 'visible'=>true),
				array('label'=>'Kedvencek', 'uri'=>'/felhasznalo/kedvencek', 'mvc'=>array('user','favourites'), 'resource'=>'kedvencek', 'visible'=>true),
				array('label'=>'Egyenlegem, számláim', 'uri'=>'/felhasznalo/egyenleg', 'mvc'=>array('user','charge'), 'resource'=>'egyenleg', 'visible'=>true),
				array('label'=>'Kijelentkezés', 'uri'=>'/felhasznalo/kijelentkezes', 'mvc'=>array('user','logout'), 'resource'=>'kijelentkezes', 'visible'=>true),
			)),
			array('label'=>'Termékfeltöltés', 'notValid'=>array('label'=>'Asztalnyitás', 'url'=>'/felhasznalo/adatmodositas?nyitas=1'), 'uri'=>'/felhasznalo/termekek?uj', 'mvc'=>array('shop','userproducts'), 'visible'=>true),
			array('label'=>'A csakbabáról', 'uri'=>'/rolunk', 'mvc'=>array('index','about'), 'resource'=>'rolunk'),
			array('label'=>'Cookie kezelési szabályzat', 'uri'=>'/cookie-szabalyzat', 'mvc'=>array('index','cookie'), 'resource'=>'cookierule', 'visible'=>false),
			array('label'=>'Blog', 'uri'=>'/blog', 'mvc'=>array('index','blog'), 'resource'=>'blog'),

			array('label'=>'Elfelejtett jelszó', 'uri'=>'/elfelejtett-jelszo', 'mvc'=>array('user','forgotten'), 'resource'=>'elfelejtett', 'visible'=>false),
			array('label'=>'Regisztráció', 'uri'=>'/regisztracio', 'mvc'=>array('user','registration'), 'resource'=>'regisztracio', 'visible'=>false, 'pages'=>array(
				array('label'=>'Aktiváció', 'uri'=>'/regisztracio/aktivacio', 'mvc'=>array('user','activation'), 'resource'=>'aktivacio', 'visible'=>false)
			)),
			array('label'=>'Bejelentkezés', 'uri'=>'/bejelentkezes', 'mvc'=>array('user','login'), 'resource'=>'bejelentkezes', 'visible'=>false),
			array('label'=>'Profil', 'uri'=>'/profil', 'mvc'=>array('user','profile'), 'visible'=>false, 'resource'=>'profil'),
			array('label'=>'Vásarlas', 'uri'=>'/vasarlas', 'mvc'=>array('market','cassa'), 'resource'=>'vasarlas', 'visible'=>false, 'pages'=>array(
				array('label'=>'Köszönjük', 'uri'=>'/vasarlas/koszonjuk', 'mvc'=>array('market','thanks'), 'resource'=>'vasarlaskoszono', 'visible'=>false)
			)),
			array('label'=>'Keresés', 'uri'=>'/kereses', 'mvc'=>array('market', 'search'), 'visible'=>false, 'resource'=>'kereses'),
			array('label'=>'Kapcsolat', 'uri'=>'/kapcsolat', 'mvc'=>array('index', 'contact'), 'visible'=>false, 'resource'=>'contact'),

			array('label'=>'ÁSZF', 'uri'=>'/aszf', 'mvc'=>array('index', 'aszf'), 'visible'=>false, 'resource'=>'aszf'),
			array('label'=>'Impresszum', 'uri'=>'/impresszum', 'mvc'=>array('index', 'impresszum'), 'visible'=>false, 'resource'=>'impresszum'),
			array('label'=>'Adatvédelem', 'uri'=>'/adatvedelem', 'mvc'=>array('index', 'adatvedelem'), 'visible'=>false, 'resource'=>'adatvedelem'),
			array('label'=>'Feliratkozva', 'uri'=>'/feliratkozva', 'mvc'=>array('index', 'feliratkozva'), 'visible'=>false, 'resource'=>'feliratkozva'),

            array('label'=>'Termék leiratkozás', 'uri'=>'/termekleiratkozas', 'mvc'=>array('index', 'commentunsubscribe'), 'visible'=>false, 'resource'=>'termekleiratkozas'),

			array('label'=>'Bejelentkezés', 'uri'=>'/slogin', 'mvc'=>array('user', 'slogin'), 'visible'=>false, 'noindex'=>true, 'resource'=>'slogin')

		));

	}

	public function findOneBy($property, $value, $useRegex = false){
		if(is_array($value)){
			$iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
			foreach ($iterator as $page) {
				$pageProperty = $page->get($property);
				if(is_array($pageProperty)){
					if($pageProperty[0]==$value['controller'] && $pageProperty[1]==$value['action']){
						return $page;
					}
				}
			}
		}
		return parent::findOneBy($property, $value, $useRegex);
	}


}
