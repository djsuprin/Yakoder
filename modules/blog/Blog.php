<?php

class Blog {

	public static $posts_count;
    public static $posts_by_page = 10;
    public static $page_number;

    public static function widget() {
		// read from cache
		$widget = Site::getFromCache('blog_widget');
		if ($widget !== false) {
			return Site::generateBlock('Популярные темы:', $widget);
		} else {
			// generate content
			$tags = array();
			$tag_strings = Site::$db->query("SELECT `tags` FROM `posts`");
			$the_heaviest_weight = 0;
			$tag_count = 0;
			foreach ($tag_strings as $tag_string) {
				$tag_array = explode(",", $tag_string['tags']);
				foreach ($tag_array as $tag) {
					$tags[trim($tag)]++;
					if ($tags[trim($tag)] > $the_heaviest_weight) {
						$the_heaviest_weight = $tags[trim($tag)];
					}
					$tag_count++;
				}
			}
			$normal = $the_heaviest_weight / $tag_count;
			$tag_iter = 0;
			foreach ($tags as $tag => $tag_weight) {
				$tag_rate = $tag_weight / $tag_count;
				$size = $tag_rate / $normal * 2 + 0.5;
				$parameters[$tag_iter]['tag'] = $tag;
				$parameters[$tag_iter]['size'] = $size;
				$tag_iter++;
			}
			ob_start();
			Site::displayView('blog', 'blog_widget.php', $parameters);
			$widget = ob_get_contents();
			ob_end_clean();
			// put the content to cache
			Site::putToCache('blog_widget', $widget);
			return Site::generateBlock('Популярные темы:', $widget);
		}
    }
	
	private static function setCurrentBlogPageNumber() {
		Blog::$page_number = 1;
		for ($i = count(Site::$args) - 1; $i > 1; $i--) {
			if (stristr(Site::$args[$i], 'blog_page') !== FALSE) {
				Blog::$page_number = substr(Site::$args[$i], 9);
				break;
			}
		}	
	}
    
    /** [action] */
    public static function showPosts() {
        // TODO: test change
		Blog::setCurrentBlogPageNumber();
	    $limit = 'LIMIT ' . ((Blog::$page_number - 1) * Blog::$posts_by_page) . ', ' . Blog::$posts_by_page;
	    $posts = Site::$db->query("
			SELECT SQL_CALC_FOUND_ROWS `posts`.*, `users`.email AS author_email, `users`.name AS author_name, `users`.avatar
			FROM `posts` LEFT JOIN `users` ON `posts`.author_id = `users`.id
			ORDER BY `posts`.`date` DESC %s", $limit);
		if (Site::$db->affectedRows() > 0) {
			$posts_count = Site::$db->query("SELECT FOUND_ROWS() AS posts_count");
			Blog::$posts_count = $posts_count[0]['posts_count'];
			$parameters['posts'] = $posts;
			Site::displayView('blog', 'post_preview.php', $parameters);
	    } else {
	    	Messager::addMessage('Нет статей.');
		}
	}
    
    /** [action] */    
    public static function showPost() {
    	Blog::showPostInternal(Site::$args[2]);
    }
    
    /** [action] */
    public static function showPostsByAuthor() {
    	Blog::showPostsByAuthorInternal(Site::$args[2]);
    }
    
    /** [action] */
    public static function showPostsByTag() {
    	Blog::showPostsByTagInternal(Site::$args[2]);
    }
    
    /** [action] */
    public static function showPostAddForm() {
    	$parameters['link'] = '/Blog/addPost';
		$parameters['form_caption'] = 'Добавление статьи';
		$parameters['button_caption'] = 'Добавить';
		$parameters['header'] = '';
		$parameters['preview'] = '';
		$parameters['text'] = '';
		$parameters['tags'] = '';
		Site::displayView('blog', 'post_form.php', $parameters);
	}
    
    /** [action] */
    public static function showPostEditForm() {
		$post_id = Site::$args[2];
		if (Blog::checkPostOwner($post_id)) {
			$posts = Site::$db->query("
				SELECT `posts`.*  , `users`.login AS author, `users`.avatar
				FROM `posts` LEFT JOIN `users` ON `posts`.author_id = `users`.id WHERE `posts`.id = %d
				GROUP BY `posts`.id ORDER BY `posts`.`date` DESC", $post_id);
			if (count($posts) > 0) {
				$parameters['link'] = '/Blog/editPost/' . $post_id;
				$parameters['form_caption'] = 'Редактирование статьи';
				$parameters['button_caption'] = 'Редактировать';
				$parameters['header'] = $posts[0]['header'];
				$parameters['preview'] = $posts[0]['preview'];
				$parameters['text'] = $posts[0]['text'];
				$parameters['tags'] = $posts[0]['tags'];
				Site::displayView('blog', 'post_form.php', $parameters);
			} else {
	            Messager::addMessage('Пост не существует.', 'error');
			}
		} else {
			Messager::addMessage('Вы можете редактировать только свои статьи');
			Site::redirect('/Blog/showPosts');
		}
	}
	
	/** [action] */
	public static function addPost() {
		if (Blog::editPostInternal($_POST['header'], $_POST['preview'], $_POST['text'],
				$_POST['tags'], 0)) {
			Blog::generateFeed();
			Site::redirect('/Blog/showPosts');
		}
		Site::redirect('/Blog/showPostAddForm');
	}
	
	/** [action] */
	public static function editPost() {
		$post_id = Site::$args[2];
		if (Blog::checkPostOwner($post_id)) {
			if (Blog::editPostInternal($_POST['header'], $_POST['preview'], $_POST['text'],
					$_POST['tags'], $post_id)) {
				Blog::generateFeed();
			}
			Site::redirect('/Blog/showPostEditForm/' . $post_id);
		} else {
			Messager::addMessage('Вы можете редактировать только свои статьи.');
		}
		Site::redirect('/Blog/showPosts');
	}
	
	/** [action] */
	public static function deletePost($post_id) {
		// Проверить, что удаляется именно статья текущего пользователя
		if (Blog::checkPostOwner($post_id)) {
			// удаляем, только если пользователь подтвердил удаление
			if (!isset(Site::$args[3]) || Site::$args[3] != 'confirmed') {
				Site::confirmForm('/blog/deletepost/' . $post_id . '/confirmed', 'Вы действительно хотите удалить эту статью?');
				return;
			}
			$was_deleted = Site::$db->query("DELETE FROM posts WHERE id = %d", $post_id);
			if ($was_deleted) {
				Site::deleteFromCache('blog_widget');
				Messager::addMessage('Статья удалена');
				Blog::generateFeed();
			} else {
			Messager::addMessage('Статью не удалось удалить', 'error');
			}
			Site::redirect('/Blog/showPosts');
		} else {
			Messager::addMessage('Вы можете удалять только свои статьи!');
			Site::redirect('/Blog/showPosts');
		}
	}

    private static function showPostInternal($post_id) {
        $posts = Site::$db->query("
            SELECT `posts`.*, `users`.email AS author_email, `users`.name AS author_name, `users`.avatar
            FROM `posts` LEFT JOIN `users` ON `posts`.author_id = `users`.id WHERE `posts`.id = %d
            GROUP BY `posts`.id ORDER BY `posts`.`date` DESC", $post_id);
        if (Site::$db->affectedRows() > 0) {
            Site::$title = $posts[0]['header'];
			$parameters = $posts[0];
            $parameters['author'] = $posts[0]['author_email'];
            if (trim($posts[0]['author_name']) != '') {
                $parameters['author'] = $posts[0]['author_name'];
            }
            $tag_string = $posts[0]['tags'];
            $parameters['tags'] = explode(',', $tag_string);
            for ($i = 0; $i < count($parameters['tags']); $i++) {
                $parameters['tags'][$i] = trim($parameters['tags'][$i]);
            }
			Site::displayView('blog', 'post.php', $parameters);
        } else {
            Messager::addMessage('Статья не существует.', 'error');
        }
    }

    private static function showPostsByAuthorInternal($author_id) {
		Blog::setCurrentBlogPageNumber();
	    $limit = 'LIMIT ' . ((Blog::$page_number - 1) * Blog::$posts_by_page) . ', ' . Blog::$posts_by_page;
        $posts = Site::$db->query("
            SELECT SQL_CALC_FOUND_ROWS `posts`.*, `users`.name AS author_name, `users`.email AS author_email, `users`.avatar
            FROM `posts` LEFT JOIN `users` ON `posts`.author_id = `users`.id WHERE `posts`.author_id = %d
            ORDER BY `posts`.`date` DESC %s", $author_id, $limit);
        if ((Blog::$posts_count = Site::$db->affectedRows()) > 0) {
			$posts_count = Site::$db->query("SELECT FOUND_ROWS() AS posts_count");
			Blog::$posts_count = $posts_count[0]['posts_count'];
            $parameters['posts'] = $posts;
			Site::displayView('blog', 'post_preview.php', $parameters);
        } else {
            $users = Site::$db->query("SELECT name, email FROM users WHERE id = %d", $author_id);
            $author_name = $users[0]['email'];
            if (trim($users[0]['name']) != '') {
                $author_name = $users[0]['name'];
            }
            Messager::addMessage($author_name . ' не имеет статей.');
        }
    }

    private static function showPostsByTagInternal($tag) {
		Blog::setCurrentBlogPageNumber();
	    $limit = 'LIMIT ' . ((Blog::$page_number - 1) * Blog::$posts_by_page) . ', ' . Blog::$posts_by_page;
        $posts = Site::$db->query("
            SELECT SQL_CALC_FOUND_ROWS `posts`.*, `users`.name AS author_name, `users`.email AS author_email, `users`.avatar
            FROM `posts` LEFT JOIN `users` ON `posts`.author_id = `users`.id WHERE `posts`.tags LIKE '%%%s%%'
            ORDER BY `posts`.`date` DESC %s", $tag, $limit);
        if ((Blog::$posts_count = Site::$db->affectedRows()) > 0) {
			$posts_count = Site::$db->query("SELECT FOUND_ROWS() AS posts_count");
			Blog::$posts_count = $posts_count[0]['posts_count'];
			$parameters['posts'] = $posts;
			Site::displayView('blog', 'post_preview.php', $parameters);
        } else {
            Messager::addMessage('Нет статей с тегом ' . $tag . '.');
        }
    }

    private static function editPostInternal($header, $preview, $text, $tags, $post_id) {
        $header = strip_tags($header);
        $text = $text;
        $tags = strip_tags($tags);
        // проверка полей
        if (trim($header) == '') {
            Messager::addMessage('Заголовок должен быть задан.', 'error');
            return false;
        } else if (trim($preview) == '') {
            Messager::addMessage('Анонс должен быть задан.', 'error');
            return false;
        }
        // добавление/редактирование поста
        if ($post_id > 0) {
            $affected_rows = Site::$db->query("
                UPDATE posts SET `header` = '%s', `preview` = '%s', `text` = '%s', `tags` = '%s', `edit_date` = %d
                WHERE `id` = %d", $header, $preview, $text, $tags, time(), $post_id);
            if ($affected_rows > 0) {
                Site::deleteFromCache('blog_widget');
                Messager::addMessage('Статья обновлена');
                return true;
            } else {
                Messager::addMessage('Статью не удалось обновить', 'error');
            }
        } else {
			$post_adding_time = time();
            $affected_rows = Site::$db->query("
                INSERT INTO posts (`author_id`, `header`, `preview`, `text`, `tags`, `date`, `edit_date`)
                VALUES (%d, '%s', '%s', '%s', '%s', %d, %d)", Site::$attrs['id'], $header, $preview, $text, $tags, $post_adding_time, $post_adding_time);
            if ($affected_rows > 0) {
                Site::deleteFromCache('blog_widget');
                Messager::addMessage('Статья добавлена');
                return true;
            } else {
                Messager::addMessage('Статью не удалось добавить', 'error');
            }
        }
        return false;
    }
	
	/** [action] */
	public static function editPreview($preview) {
		$post_id = Site::$args[2];
		if (Blog::checkPostOwner($post_id)) {
			$affected_rows = Site::$db->query("UPDATE posts SET `preview` = '%s', `edit_date` = %d WHERE `id` = %d", 
					$preview, time(), $post_id);
			if ($affected_rows > 0) {
				echo '{"data": {"result":"done", "message":"Анонс статьи изменен"}}';
			} else {
				echo '{"data": {"result":"error", "message":"Не удалось изменить анонс статьи"}}';
            }
		} else {
			echo '{"data": {"result":"error", "message":"Вы не можете править чужую статью"}}';
		}
		die();
	}
	
	/** [action] */
	public static function editText($text) {
		$post_id = Site::$args[2];
		if (Blog::checkPostOwner($post_id)) {
			$affected_rows = Site::$db->query("UPDATE posts SET `text` = '%s', `edit_date` = %d WHERE `id` = %d", 
					$text, time(), $post_id);
			if ($affected_rows > 0) {
				echo '{"data": {"result":"done", "message":"Текст статьи изменен"}}';
			} else {
				echo '{"data": {"result":"error", "message":"Не удалось изменить текст статьи"}}';
            }
		} else {
			echo '{"data": {"result":"error", "message":"Вы не можете править чужую статью"}}';
		}
		die();
	}

    public static function checkPostOwner($post_id) {
        $posts = Site::$db->query("SELECT * FROM posts WHERE id = %d AND author_id = %d", $post_id, Site::$attrs['id']);
        if (Site::$db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function pageNavigator($page_arg_number) {
		
        if (Blog::$posts_count > Blog::$posts_by_page) {
            $parameters['pages_count'] = ceil(Blog::$posts_count / Blog::$posts_by_page);
			/*if ( ($pos = stripos($_SERVER["REQUEST_URI"], 'blog_page')) !== FALSE) {
				$link = substr($_SERVER["REQUEST_URI"], 0, $pos - 1) . '/';
			} else {
			    $link = $_SERVER["REQUEST_URI"] . 'blog/showposts/';
			}*/
            if ((Blog::$page_number - 1) > 0) {
				$parameters['previous_page_href'] = '/' . Site::$args[0] . '/' . Site::$args[1] . '/blog_page' . (Blog::$page_number - 1);
            }
            if ((Blog::$page_number + 1) < ($parameters['pages_count'] + 1)) {
				$parameters['next_page_href'] = '/' . Site::$args[0] . '/' . Site::$args[1] . '/blog_page' . (Blog::$page_number + 1);
            }
			$parameters['page_number'] = Blog::$page_number;
			Site::displayView('blog', 'page_navigator.php', $parameters);
        }
    }
	
	public static function generateFeed()
	{
		$posts = Site::$db->query("SELECT * FROM posts ORDER BY id DESC LIMIT 10");
		$file_content = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
	<channel>
		<title>Блоголента сайта Yakoder.ru</title>
		<link>http://www.yakoder.ru</link>
		<description>Блог о программировании и информационных технологиях</description>
		<lastBuildDate>' . date("r", time()) . '</lastBuildDate>
		';
		foreach ($posts as $post)
		{
			$post['preview'] = str_ireplace("</p>", "</p>\r\n", $post['preview']);
			$preview = trim(strip_tags($post['preview']));
			$file_content .= '
		<item>
			<title>' . $post['header'] . '</title>
			<link>http://www.yakoder.ru/Blog/showPost/' . $post['id'] . '</link>
			<description>' . $preview . '</description>
		</item>
			';
		}
		$file_content .= '
	</channel>
</rss>
		';
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/rss.xml', $file_content);
	}

	/** [action] [allowed] */
	public static function goToFeed()
	{
		Site::redirect('/rss.xml?' . time());
	}

}

?>
