<?php
class BaiduSitemap_HtmlGenerator extends Typecho_Widget implements Widget_Interface_Do
{
	public function action()
	{
		$html = file_get_contents(dirname(__FILE__)."/sitemap.html");		
		$db = Typecho_Db::get();
		$options = Typecho_Widget::widget('Widget_Options');

		$article_contents = '';
		$articles = $db->fetchAll($db->select()->from('table.contents')
					->where('table.contents.status = ?', 'publish')
					->where('table.contents.created < ?', $options->gmtTime)
					->where('table.contents.type = ?', 'post')
					->order('table.contents.created', Typecho_Db::SORT_DESC));
		foreach($articles AS $article) {
			$type = $article['type'];
			$article['categories'] = $db->fetchAll($db->select()->from('table.metas')
					->join('table.relationships', 'table.relationships.mid = table.metas.mid')
					->where('table.relationships.cid = ?', $article['cid'])
					->where('table.metas.type = ?', 'category')
					->order('table.metas.order', Typecho_Db::SORT_ASC));
			$article['category'] = urlencode(current(Typecho_Common::arrayFlatten($article['categories'], 'slug')));
			$article['slug'] = urlencode($article['slug']);
			$article['date'] = new Typecho_Date($article['created']);
			$article['year'] = $article['date']->year;
			$article['month'] = $article['date']->month;
			$article['day'] = $article['date']->day;
			$routeExists = (NULL != Typecho_Router::get($type));
			$article['pathinfo'] = $routeExists ? Typecho_Router::url($type, $article) : '#';
			$article['permalink'] = Typecho_Common::url($article['pathinfo'], $options->index);
			$article_contents .= '<li><a href="'.$article['permalink'].'" title="'.$article['title'].'" target="_blank">'.$article['title'].'</a></li>'."\n";
		}
		
		$page_contents = '';
		$pages = $db->fetchAll($db->select()->from('table.contents')
				->where('table.contents.status = ?', 'publish')
				->where('table.contents.created < ?', $options->gmtTime)
				->where('table.contents.type = ?', 'page')
				->order('table.contents.created', Typecho_Db::SORT_DESC));
		foreach($pages AS $page) {
			$type = $page['type'];
			$routeExists = (NULL != Typecho_Router::get($type));
			$page['pathinfo'] = $routeExists ? Typecho_Router::url($type, $page) : '#';
			$page['permalink'] = Typecho_Common::url($page['pathinfo'], $options->index);
			$page_contents .= '<li><a href="'.$page['permalink'].'" title="'.$page['title'].'" target="_blank">'.$page['title'].'</a></li>'."\n";
		}
		
		$category_contents = '';
		$categorys = $db->fetchAll($db->select()->from('table.metas')
					->where('table.metas.type = ?', 'category')
					->order('table.metas.order', Typecho_Db::SORT_ASC));
		foreach ($categorys as $category) {
			$type = $category['type'];
			$routeExists = (NULL != Typecho_Router::get($type));
			$category['pathinfo'] = $routeExists ? Typecho_Router::url($type, $category) : '#';
			$category['permalink'] = Typecho_Common::url($category['pathinfo'], $options->index);
			$category_contents .= '<li><a href="'.$category['permalink'].'" title="'.$category['name'].'" target="_blank">'.$category['name'].'</a></li>'."\n";
		}
		
		$html = str_replace("%blog_title%", "站点地图", $html);
		$html = str_replace("%blog_name%", $options->title, $html);
		$html = str_replace("%blog_home%", $options->siteUrl, $html);
		$html = str_replace("%sitemap_url%",$options->siteUrl."sitemap.html",$html);
		$html = str_replace("%updated_time%", date('Y-m-d H:i:s'), $html);
		$html = str_replace("%article_contents%",$article_contents,$html);  // articles
		$html = str_replace("%category_contents%",$category_contents,$html);  // category
		$html = str_replace("%page_contents%",$page_contents,$html);  // page
		echo $html;
	}
}
