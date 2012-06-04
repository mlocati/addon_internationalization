<?php
Loader::Model('section', 'multilingual');

class TranslatedPagesHelper {

	public function addMetaTags() {
		$v      = View::getInstance();
		$translations = self::getTranslatedPages();
		foreach ($translations as $locale => $tpage) {
			$v->addHeaderItem(
				self::altMeta($locale,$tpage)
			);
		}
	}

	public function getTranslatedPages() {
		$page   = Page::getCurrentPage();
		$langms = new MultilingualSection;
		$ms     = MultilingualSection::getCurrentSection();
		if (is_object($ms))
			$lang = $ms->getLocale();
		$locales  = self::getLocales($lang);

		$tpages = array();
		foreach($locales as $locale) {
			$langms->msLocale = $locale;
			$id = $langms->getTranslatedPageID($page);
			$transPage = Page::getByID($id);
			$transPage->locale = $locale;
			if ($id > 0) {
				$tpages[$locale] = $transPage;
			}
		}
		return $tpages;
	}

	public function altMeta($lang,$page) {
		list($iso63911,$country) = explode('_',strtolower($lang));
		$nh = Loader::helper('navigation');
		$uri = $nh->getCollectionURL($page);
		$meta = array(
			'link',
			'rel'=>'alternate',
			'hreflang'=>$iso63911."-".$country,
			'href'=>$uri
		);
		return self::renderTag($meta);
	}

	protected function renderTag($arr) {
		$tag = array_shift($arr);
		foreach($arr as $attr=>$val) $tag.=" {$attr}=\"{$val}\"";
		return "<{$tag} />";
	}

	public function getLocales($sans) {
		$db = Loader::db();
		$page = Page::getCurrentPage();
		$query = $db->query('SELECT mpLocale FROM MultilingualPageRelations');
		$locales = array();
		while ($row = $query->FetchRow())
			if ($row['mpLocale'] != $sans)
				$locales[] = $row['mpLocale'];
		return $locales;
	}

}