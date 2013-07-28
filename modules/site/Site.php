<?php

class Site {
	
	// Default action parameters
    public static $default_module = "blog";
    public static $default_action = "showposts";
	
	// Default view
	public static $view = "yakoder";
	
	// Default title
    public static $title = "Сайт программиста Якодер.ру";
	
	// Default timezone
    //public static $timezone = 'Europe/Moscow';

    public static $attrs;
    
	// this field stores array of action arguments, i.e. action /blog/showposts/2 converts to ['blog', 'showposts', '2']
	public static $args;
	
	// this field references DataBase object
    public static $db;
    public static $authorized;
	
	// this field stores generated HTML code for widgets area
    public static $widgets;
	
	// this field stores generated HTML code for main content area
    public static $content;
	
	// this field stores html <link> and <script> tags to add JS and CSS code to the page
	public static $head_code;
	
	// this field stores function doc comment which should contain special annotations, i.e. [action] or [allowed]
	public static $annotation_string;

	// this function generates and returns widget's HTML code for Site module
    public static function widget() {
		ob_start();
		Site::loginBlock();
		$widget = ob_get_contents();
		ob_end_clean();
		return Site::generateBlock('', $widget);
    }
	
	public static function putToCache($key, $data, $overwrite = true) {
		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/cached/')) {
			mkdir($_SERVER['DOCUMENT_ROOT'] . '/cached/');
			@chmod($_SERVER['DOCUMENT_ROOT'] . '/cached/', 0777); // TODO change to 0775 and test it!!!
		}
		$fileExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/cached/' . $key . '.html');
		if (!$fileExists || ($fileExists && $overwrite)) {
			return file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cached/' . $key . '.html', $data);
		} else {
			return false;
		}
	}
	
	public static function getFromCache($key) {
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/cached/' . $key . '.html')) {
			$content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cached/' . $key . '.html');
			return $content;
		} else {
			return false;
		}
	}
	
	public static function deleteFromCache($key) {
		return unlink($_SERVER['DOCUMENT_ROOT'] . '/cached/' . $key . '.html');
	}
	
	public static function clearCache() {
		// untested
		array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/cached/*'));
	}
	
	// case insensitive implementation of file_exists
	private static function file_exists($path) {
		$dir_name = dirname($path);
		$file_name = basename($path);
		if (is_dir($dir_name)) {
			if ($dir = opendir($dir_name)) {
				while (($next_file = readdir($dir)) !== false) {
					if ($next_file != '.' && $next_file != '..' && !is_dir($next_file) && 
							strtolower($next_file) == strtolower($file_name)) {
						return $next_file;
					}
				}
			}
		}
		return false;
	}

    public static function start() {
		// debug mode is currently ON (to turn it off pass only E_ERROR to the function below)
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		//date_default_timezone_set(Site::$timezone);
        require 'Messager.php';
        // Готовим соединение с базой данных
        require 'DB.php';
        Site::$db = new DB(DB::HOST, DB::USER, DB::PASSWORD, DB::SCHEMA);
        // Готовим аргументы
		if (isset($_GET['args'])) {
			Site::$args = explode("/", $_GET['args']);
		}
        // Проводим классическую авторизацию
        Site::authenticate();
		// Или проводим авторизацию по OpenID
        //Site::authorizeByOpenID();
        // Готовим контент
        ob_start();
        if (trim(Site::$args[0]) == '') {
            Site::$args[0] = Site::$default_module;
            Site::$args[1] = Site::$default_action;
        }
		$module_lower = strtolower(Site::$args[0]);
		$action_lower = strtolower(Site::$args[1]);
        if (($file_name = Site::file_exists('./modules/' . $module_lower . '/' . $module_lower . '.php')) != false) {
            require_once './modules/' . $module_lower . '/' . $file_name;
            try {
        	$reflection_method = new ReflectionMethod($module_lower, $action_lower);
				Site::$annotation_string = $reflection_method->getDocComment();
                // вызываем метод, только если у него имеется аннотация [action]
                if (strpos(Site::$annotation_string, '[action]') !== false) {
                	$reflection_parameters = $reflection_method->getParameters();
                	// привязать параметры к элементам массивов GET, POST, FILES и аргументов командной строки
                	$parameters = array();
                	$current_args_index = 2;
                	$noParameterErrors = true;
                	foreach ($reflection_parameters as $reflection_parameter) {
                	    if (isset($_GET[$reflection_parameter->name])) {
							$parameters[] = $_GET[$reflection_parameter->name];
                	    } else if (isset($_POST[$reflection_parameter->name])) {
							$parameters[] = $_POST[$reflection_parameter->name];
                	    } else if (isset($_FILES[$reflection_parameter->name])) {
							$parameters[] = $_FILES[$reflection_parameter->name];
                	    } else if (isset(Site::$args[$current_args_index])) {
							$parameters[] = Site::$args[$current_args_index++];
                	    } else {
							// сперва проверить, нет ли у параметра значения по умолчанию
							$noParameterErrors = false;
                	    }
                	}
                	// вызвать метод с параметрами
                	if ($noParameterErrors) {
						// Запрашиваем право на вызов метода
						if (Site::isAllowed($action_lower, $module_lower)) {
							$reflection_method->invokeArgs(new ReflectionClass($module_lower), $parameters);
						} else {
							Messager::addMessage('У вас нет необходимых прав для просмотра этой страницы', 'error');
						}
                	} else {
                	    Messager::addMessage('Неправильно заданы параметры для действия', 'error');
						Site::page_not_found();
                	}
                } else {
                	//Messager::addMessage('Неизвестное действие', 'error');
					Site::page_not_found();
                }
                // end call
            } catch (Exception $ex) {
                //Messager::addMessage('Страница не найдена', 'error');
                Site::page_not_found();
            }
        } else {
            //Messager::addMessage('Неизвестный модуль', 'error');
			Site::page_not_found();
        }
        Site::$content .= ob_get_contents();
        ob_end_clean();
        // Готовим страницу
		Site::displayView('site', Site::$view . '.php');
        // Отключаемся от базы данных
        Site::$db->disconnect();
    }

    private static function authenticate() {
        // test change
		session_start();
        Site::$authorized = false;
		// Log out was initiated
        if (isset($_GET['exit'])) {
            session_unset();
            setcookie('site_login', '', time() - 3600, '/');
            setcookie('site_password', '', time() - 3600, '/');
            Messager::addMessage('Вы вышли из системы');
			Site::redirect('/');
            return;
		// User is already authenticated by openid
        } else if (isset($_SESSION['openid']) && $_SESSION['openid'] != '') {
            Site::$attrs['contact/email'] = $_SESSION['email'];
            Site::fillAttrs();
            Site::$authorized = true;
            return;
        } else {
			// Trying to collect user's login and password
			if (isset($_COOKIE['site_login']) && isset($_COOKIE['site_password'])) {
				$site_login = $_COOKIE['site_login'];
				$site_password = $_COOKIE['site_password'];
			} else if (isset($_POST['site_login']) && isset($_POST['site_password'])) {
				$site_login = $_POST['site_login'];
				$site_password = md5($_POST['site_password']);
			} else if (isset($_GET['site_login']) && isset($_GET['site_password'])) {
				$site_login = $_GET['site_login'];
				$site_password = md5($_GET['site_password']);
			}
			
			// User's login and password are available
			if ($site_login != '' && $site_password != '') {
				$users = Site::$db->query("SELECT * FROM users WHERE `login` = '%s' AND `password` = '%s'", $site_login, $site_password);
				if (Site::$db->affectedRows() > 0) {
					setcookie('site_login', $users[0]['login'], time() + 3600, '/');
					setcookie('site_password', $users[0]['password'], time() + 3600, '/');					
					Site::$attrs['id'] = $users[0]['id'];
					Site::$attrs['user_id'] = $users[0]['id'];
					Site::$attrs['name'] = $users[0]['name'];
					Site::$attrs['login'] = $users[0]['login'];
					Site::$attrs['email'] = $users[0]['email'];
					Site::$attrs['avatar'] = $users[0]['avatar'];
					Site::$authorized = true;
					return;
				} else {
					Messager::addMessage('Неверный логин или пароль', 'error');
				}
			}
			
			// Authentication by OpenID
			require 'openid.php';
			$openid = new LightOpenID('www.yakoder.ru');
			$openid->required = array('contact/email');
			$openid->optional = array('namePerson', 'namePerson/friendly');
			Site::$authorized = false;
			
			try {
				// User maybe initiated OpenID authentication
				if (!$openid->mode) {
					if (isset($_GET['by_google'])) {
						$openid->identity = 'https://www.google.com/accounts/o8/id';
					} else if (isset($_GET['by_yandex'])) {
						$openid->identity = 'http://openid.yandex.ru/';
					} else {
						// User did NOT initiate authentication
						return;
					}
					header('Location: ' . $openid->authUrl());
				// User was successfully authenticated by OpenID
				} else if ($openid->mode == 'id_res') {
					$is_valid = $openid->validate();
					Site::$attrs = $openid->getAttributes();
					if ($is_valid) {
						$_SESSION['openid'] = $openid->identity;
						Site::fillAttrs();
						Site::$authorized = true;
						// TODO переходить на ту же страницу, но с вырезанными get параметрами openid.
					}
				}
			// Error happened during authenticating
			} catch (ErrorException $e) {
				Messager::addMessage('Ошибка во время аутентификации', 'error');
			}
        }
    }

    private static function fillAttrs() {
        $users = Site::$db->query("SELECT id, name, login, email, avatar FROM users WHERE openid = '%s'",
                $_SESSION['openid']);
        if (Site::$db->affectedRows() <= 0) {
            Site::$db->query("INSERT INTO users (openid, name, login, email, avatar, registration_date)
                VALUES ('%s', '%s', '%s', '%s', '%s', %d)",
                    $_SESSION['openid'], Site::$attrs['namePerson'], Site::$attrs['namePerson/friendly'],
                    Site::$attrs['contact/email'], '/images/avatars/user.png', time());
            $users = Site::$db->query("SELECT id, name, login, email, avatar
                FROM users WHERE openid = '%s'", $_SESSION['openid']);
            Site::$db->query("INSERT INTO users_groups (user_id, group_id)
                VALUES (%d, %d)", $users[0]['id'], 6);
            Site::deleteFromCache('site_widget');
        }
        Site::$attrs['id'] = $users[0]['id'];
        Site::$attrs['user_id'] = $users[0]['id'];
        Site::$attrs['name'] = $users[0]['name'];
        Site::$attrs['login'] = $users[0]['login'];
        Site::$attrs['email'] = $users[0]['email'];
        Site::$attrs['avatar'] = $users[0]['avatar'];
    }

    public static function getName() {
        if (Site::$attrs['name'] != '') {
            echo Site::$attrs['name'];
        }
        else if (Site::$attrs['login'] != '') {
            echo Site::$attrs['login'];
        }
        else {
            echo Site::$attrs['email'];
        }
    }

    private static function generatePassword($length = 8) {
        $password = "";
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
        $maxlength = strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        return $password;
    }
	
	public static function showWidgets($widgets) {
		// Готовим виджеты
		$widgets_html = '';
        //$widgets = Site::$db->query("SELECT * FROM widgets ORDER BY priority");
        foreach ($widgets as $module => $widgetFunctions) {
            if (file_exists('./modules/' . strtolower($module) . '/' . $module . '.php')) {
                require_once './modules/' . strtolower($module) . '/' . $module . '.php';
                $reflection_object = new ReflectionClass($module);
				if (!is_array($widgetFunctions)) {
					$widgetFunction = $widgetFunctions;
					if ($reflection_object->hasMethod($widgetFunction)) {
						$widgets_html .= $reflection_object->getMethod($widgetFunction)->invoke($module);
					}
				} else {
					foreach ($widgetFunctions as $widgetFunction) {
						if ($reflection_object->hasMethod($widgetFunction)) {
							$widgets_html .= $reflection_object->getMethod($widgetFunction)->invoke($module);
						}
					}
				}
            }
        }
		echo $widgets_html;
	}

    public static function isAdmin() {
        return Site::inGroup('Админы');
    }

    public static function inGroup($group_caption) {
        $users_groups = Site::$db->query("
                SELECT * FROM `users_groups` LEFT JOIN `groups` ON `groups`.`id` = `users_groups`.`group_id`
                WHERE `groups`.`caption` = '%s' AND `users_groups`.`user_id` = %d", $group_caption, Site::$attrs['id']);
        if (Site::$db->affectedRows() > 0) {
            return true;
        }
        return false;
    }

    public static function isAllowed($action, $module) {
		if (strpos(Site::$annotation_string, '[allowed]') !== false) {
			return true;
		}
        $permissions = Site::$db->query("
                SELECT * FROM `permissions` WHERE LOWER(action_name) = '%s' AND LOWER(module_name) = '%s'
                    AND (group_id IN (SELECT group_id FROM users_groups WHERE user_id = %d)
                    OR group_id IN (SELECT id FROM groups WHERE caption = 'Гости'))", 
				strtolower($action), strtolower($module), Site::$attrs['id']);
        if (Site::$db->affectedRows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /** [action] */
    public static function userInfo($user_id) {
        $users = Site::$db->query("SELECT * FROM users WHERE id = %d", $user_id);
        if (Site::$db->affectedRows() > 0) {
            $parameters['user'] = $users[0];
            if (trim($users[0]['name']) != '') {
                $parameters['user']['display_name'] = $users[0]['name'];
            }
            else if (trim($users[0]['login']) != '') {
                $parameters['user']['display_name'] = $users[0]['login'];
            }
            else if (trim($users[0]['email']) != '') {
                $parameters['user']['display_name'] = $users[0]['email'];
            }
            else {
                $parameters['user']['display_name'] = $users[0]['openid'];
            }
			Site::displayView('site', 'show_user.php', $parameters);
        } else {
            Messager::addMessage('Неизвестный пользователь', 'error');
        }
    }
    
    /** [action] */
    public static function showUsers() {
        $parameters['users'] = Site::$db->query("SELECT * FROM `users` ORDER BY avatar, name DESC");
		Site::displayView('site', 'show_users.php', $parameters);
    }
    
    /** [action] */
    public static function profileForm($user_id) {
        $users = Site::$db->query("SELECT * FROM users WHERE id = %d", $user_id);
        if (Site::$db->affectedRows() > 0) {
			$parameters['user'] = $users[0];
			Site::displayView('site', 'profile_form.php', $parameters);
        } else {
            Messager::addMessage('Неизвестный пользователь');
        }
    }

    /** [action] */
    public static function editProfile($user_id) {
        $users = Site::$db->query("SELECT * FROM `users` WHERE id = %d", $user_id);
        if (trim($_POST['new_name']) != '') {
            Site::$db->query("UPDATE `users` SET `name` = '%s' WHERE id = %d", 
            	strip_tags($_POST['new_name']), $user_id);
            Messager::addMessage('Имя изменено');
        }
		if ($_FILES['new_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
			Site::changeAvatar($user_id, $_FILES['new_avatar']);
		}
        Site::deleteFromCache('site_widget');
        Site::redirect('/Site/profileForm/' . $user_id);
    }

    public static function uploadImage($file, $parameters = '') {
        if (!isset($parameters['file_size'])) {
            $parameters['file_size'] = 2048000;
        }
        if (!isset($parameters['allowed_mime_types'])) {
            $parameters['allowed_mime_types'] = array('image/jpeg', 'image/gif', 'image/png');
        }
        if (!isset($parameters['destination'])) {
            $parameters['destination'] = '/images/';
        }
        if (!isset($parameters['file_name'])) {
            $parameters['file_name'] = time();
        }
        if (!isset($parameters['replace'])) {
            $parameters['replace'] = false;
        }

        $file_size = filesize($file['tmp_name']);
        if ($file_size > $parameters['file_size']) {
            // Размер файла слишком большой
            return false;
        }
        $image_size = getimagesize($file['tmp_name']);
        if (!$image_size) {
            // Загружаемый файл не является картинкой
            return false;
        }

        if (!in_array($file['type'], $parameters['allowed_mime_types'])) {
            // Загружаемое изображение имеет неподдерживаемый тип
            return false;
        }

        // Изменяем размер изображения
        $min_size = min($image_size[0], $image_size[1]);
        $avatar_resource = imagecreatetruecolor(50, 50);
        switch ($file['type'])
        {
            case 'image/jpeg': 
                $temp_resource = imagecreatefromjpeg($file['tmp_name']);
                imagecopyresampled($avatar_resource, $temp_resource, 0, 0, 0, 0, 50, 50, $min_size, $min_size);
                imagejpeg($avatar_resource, $file['tmp_name']);
                break;
            case 'image/png': 
                $temp_resource = imagecreatefrompng($file['tmp_name']);
                imagecopyresampled($avatar_resource, $temp_resource, 0, 0, 0, 0, 50, 50, $min_size, $min_size);
                imagepng($avatar_resource, $file['tmp_name']);
                break;
            case 'image/gif': 
                $temp_resource = imagecreatefromgif($file['tmp_name']);
                imagecopyresampled($avatar_resource, $temp_resource, 0, 0, 0, 0, 50, 50, $min_size, $min_size);
                imagegif($avatar_resource, $file['tmp_name']);
                break;
        }

        // Получаем расширение файла
        $ext = substr($file['name'], strrpos($file['name'], '.'));
        // Копирование изображение в папку с изображениями
        $destination_path = $_SERVER['DOCUMENT_ROOT'] . $parameters['destination'];
        $file_name = $parameters['file_name'] . $ext;
        $path_to_image = $destination_path . '/' . $file_name;
        $path_to_image = str_replace('//', '/', $path_to_image);
        if (file_exists($path_to_image)) {
            if ($parameters['replace'] == true) {
                if (!unlink($path_to_image)) {
                    // Не удалось удалить старое изображение
                    return false;
                }
            } else {
                // Файл с таким именем уже существует
                return false;
            }
        }
        if (!copy($file['tmp_name'], $path_to_image)) {
            // Не удалось загрузить изображение
            //die();
            return false;
        }
        // изображение успешно загружено
        return $file_name;
    }

    public static function changeAvatar($user_id, $avatar) {
        if ($filename = Site::uploadImage($avatar, array('destination' => '/images/avatars/')))
        {
            // Изменить ссылку на аватар в БД
            if (Site::$db->query("UPDATE users SET avatar = '%s' WHERE id = %d", '/images/avatars/' . $filename, $user_id))
            {
                Messager::addMessage('Аватар успешно изменен');
            }
            else
            {
                Messager::addMessage('Не удалось изменить аватар', 'error');
            }
        }
        else
        {
            Messager::addMessage('Не удалось загрузить аватар', 'error');
        }
    }

    /** [action] */
    public static function showPermissionsList() {
        $groups = Site::$db->query("SELECT * FROM `groups` ORDER BY caption");
        $permissions = Site::$db->query("SELECT * FROM `permissions` ORDER BY action_name");
        $modules_files = scandir('modules');
        $modules_count = count($modules_files);
        for ($i = 2; $i < $modules_count; $i++) {
            if (($file_name = Site::file_exists($_SERVER['DOCUMENT_ROOT'] . '/modules/' . $modules_files[$i] . '/' . $modules_files[$i] . '.php')) != false) {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/' . $modules_files[$i] . '/' . $file_name;
                $file_name_array = explode('.', $file_name);
                $module_name = $file_name_array[0];
                $modules[$module_name] = array();
                $reflection_object = new ReflectionClass($module_name);
                // TODO необходимо получить только список методов с аннотацией [action]
                // получили список всех методов
                $all_methods = $reflection_object->getMethods();
                $methods = array();
                // в цикле выбрали те, у которых есть аннотация [action]
                foreach ($all_methods as $method) {
            	    if (strpos($method->getDocComment(), '[action]')) {
            		$methods[] = $method;
            	    }
                }
                sort($methods);
                foreach ($methods as $method) {
                    foreach ($groups as $group) {
                        if (in_array(array('group_id' => $group['id'], 'module_name' => $module_name, 'action_name' => $method->name), $permissions) == true) {
                            $modules[$module_name][$method->name][$group['caption']] = "checked";
                        } else {
                            $modules[$module_name][$method->name][$group['caption']] = "";
                        }
                    }
                }
            }
        }
		$parameters['modules'] = $modules;
		$parameters['groups'] = $groups;
        Site::displayView('site', 'show_permissions.php', $parameters);
    }

    /** [action] */
    public static function changePermissions() {
    	$permissions = $_POST['permissions'];
        $groups_array = Site::$db->query("SELECT * FROM `groups` ORDER BY caption");
        foreach ($groups_array as $group) {
            $groups[$group['caption']] = $group['id'];
        }
        Site::$db->query("DELETE FROM `permissions`");
        foreach ($permissions as $module_name => $method_names) {
            foreach ($method_names as $method_name => $group_captions) {
                foreach ($group_captions as $group_caption => $permission) {
                    Site::$db->query("INSERT INTO `permissions` (`group_id`, `module_name`, `action_name`) VALUES (%d, '%s', '%s')",
                            $groups[$group_caption], $module_name, $method_name);
                }
            }
        }
        Messager::addMessage('Список разрешений изменен.');
        Site::redirect('/Site/showPermissionsList');
    }

    public static function loginBlock() {
        if (Site::$authorized) {
			Site::displayView('site', 'logged_block.php');
        } else {
			Site::displayView('site', 'login_form.php');
        }
    }

    public static function generateBlock($block_header, $block_content) {
		$parameters['block_header'] = trim($block_header);
		$parameters['block_content'] = $block_content;
        ob_start();
        Site::displayView('site', 'widget.php', $parameters);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

	// do we need that function?
    public static function prepareString($string) {
        return strip_tags($string, '<a><b><i><u><p><ul><ol><li><img><br><table><tr><td>');
    }

    public static function redirect($link) {
        header("Location: " . $link);
        die();
    }

    public static function goBack() {
        self::redirect($_SERVER['HTTP_REFERER']);
    }

    public static function checkEmail($email) {
        $pattern = "%^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z])+$%";
        $res = preg_match($pattern, $email);
        return (bool) $res;
    }

    public static function confirmForm($action_confirmed, $caption, $action_denied = '') {
		$parametersConfirmed['action_confirmed'] = $action_confirmed;
		$parametersConfirmed['caption'] = $caption;
		$parametersDenied['action_denied'] = $action_denied;
		Site::displayView('site', 'confirmFormConfirmed.php', $parametersConfirmed);
        if (trim($action_denied) != '') {
            Site::displayView('site', 'confirmFormDenied.php', $parametersDenied);
        }
    }
    
    public static function displayView($module, $view, $parameters = '') {
    	$path_to_view = $_SERVER['DOCUMENT_ROOT'] . '/modules/' . $module . '/view/' . Site::$view . '/' . $view;
    	if (Site::file_exists($path_to_view) !== false) {
    		require $path_to_view;
    	} else if (Site::file_exists($_SERVER['DOCUMENT_ROOT'] . '/modules/' . $module . '/view/' . $view)) {
    		require $_SERVER['DOCUMENT_ROOT'] . '/modules/' . $module . '/view/' . $view; 
    	}
    }
	
	public static function addHeadCode($code) {
		if (strpos(Site::$head_code, $code) === FALSE) {
			Site::$head_code .= $code;
		}
	}
	
	/** [action] */
	public static function page_not_found() {
		Site::displayView('site', '404.php');
	}

}

?>
