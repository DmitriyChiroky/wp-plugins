Plugins development and modification.

## List of WordPress Plugins

---

### Bob Single Sync Info
[Code - GitHub](https://github.com/DmitriyChiroky/wp-plugins/tree/main/bob-single-sync-info)

Описание.
Плагин для синхронизации данных об отелях из сервисов Google Place Details, TripAdvisor, SerpApi, для получения необходимой информации и формирования рейтинга.  

Функции:
- получение и обновление данных об отеле: цена, рейтинг, количество оценок, адрес, класс отеля. Синхронизация происходит путем нажатия на кнопки в посте -  Google Sync, TripAdvisor Sync and SerpApi Sync Info.
- формирование общего рейтинга. При обновлении поста производится суммирование всех рейтингов и выводится средний по определенной формуле.
- обновление рейтинга и цен на отели каждую неделю, использую крон-задачу.
общего.
