# InstaStories Saver

Программа для сохранения историй Instagram. 

Всё предельно просто, скачиваем архив с программой, распаковываем. Устанавливаем PHP:

* Windows: скачиваем отсюда архив: [http://windows.php.net/download#php-7.2](http://windows.php.net/download#php-7.2), распаковываем куда-либо, добавляем в **Path** адрес к папке с **php.exe** (либо распаковываем все файлы в папку **bin** скачанного архива), скачиваем [php.ini](https://raw.githubusercontent.com/Ivan-Alone/imageres-storage/master/php.ini) и кладём в папку с **php.exe**
* Linux Debian/Ubuntu: 
```sudo apt install php php-curl php-gd```

На \*NIX'ах настраиваем разрешение на выполнение файла **InstaStories.sh**, например так: 
```chmod 777 InstaStories.sh```

Далее запускаем файл, зависящий от ОС (**InstaStories.sh** - **\*NIX**, **InstaStories.cmd** - **Windows**), и видим следующее окно:

![](https://ivan-alone.github.io/imageres-storage/%D0%A1%D0%BD%D0%B8%D0%BC%D0%BE%D0%BA%20%D1%8D%D0%BA%D1%80%D0%B0%D0%BD%D0%B0%20(65).png)

Экран загрузки (декоративный, на самом деле) =)

Далее вы увидите программу:

![](https://ivan-alone.github.io/imageres-storage/%D0%A1%D0%BD%D0%B8%D0%BC%D0%BE%D0%BA%20%D1%8D%D0%BA%D1%80%D0%B0%D0%BD%D0%B0%20(66).png)

Вводим логин, пароль и... Программа загружает все доступные в ленте на данный момент истории! Их можно найти п папке **Instagram**, распределённые по пользователям.

![](https://ivan-alone.github.io/imageres-storage/testview.png)

Программа запоминает ваши данные авторизации, и далее вы сможете докачивать истории просто запустив программу и немного подождав. 

Если что-то сломалось - в первую очередь удалите папку **temp** и пройдите авторизацию заново. Если проблема не исчезла - обращайтесь с Pull-request'ом здесь, на GitHub.

Всем спасибо за использование моей программы.

UDP. Скриншот теста на Ubuntu Linux 
![](https://ivan-Alone.github.io/imageres-storage/onLinux.png)
