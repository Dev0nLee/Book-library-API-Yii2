function searchBooks() {
    var query = $('#searchQuery').val().trim();
    if (!query) {
        $('#searchResults').html('<div class="error-message">Пожалуйста, введите запрос для поиска</div>');
        return;
    }

    $.ajax({
        url: bookSearchAjaxUrl,
        type: 'GET',
        data: { q: query },
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.books);
            } else {
                $('#searchResults').html('<div class="error-message">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#searchResults').html('<div class="error-message">Произошла ошибка при поиске книг</div>');
        }
    });
}

function displaySearchResults(books) {
    var html = '';
    if (books.length === 0) {
        html = '<div class="error-message">Книги не найдены</div>';
    } else {
        $.each(books, function(index, book) {
            html += '<div class="book-card">';
            if (book.image) {
                html += '<img src="' + book.image + '" alt="Обложка">';
            }
            html += '<div class="book-info">';
            html += '<h3>' + book.title + '</h3>';
            html += '<p><strong>Авторы:</strong> ' + (book.authors.join(', ') || 'Неизвестный автор') + '</p>';
            html += '<p><strong>Описание:</strong> ' + (book.description || 'Описание отсутствует') + '</p>';
            html += '<p><strong>Дата публикации:</strong> ' + (book.publishedDate || 'Не указана') + '</p>';
            html += '<p><strong>Страниц:</strong> ' + (book.pageCount || 'Не указано') + '</p>';
            html += '<div class="book-actions">';
            html += '<a href="' + book.url + '" class="btn btn-info btn-sm" target="_blank">Подробнее</a> ';
            html += '<button class="btn btn-success btn-sm" onclick="saveBook(\'' + book.id + '\', \'' + book.source + '\')">Сохранить</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
    }
    $('#searchResults').html(html);
}

function saveBook(id, source) {
    var data = {};
    data[csrfParam] = csrfToken;
    data.id = id;
    data.source = source;

    $.ajax({
        url: bookSaveAjaxUrl,
        type: 'POST',
        data: data,
        success: function(response) {
            if (response.status === 'success') {
                $('#searchResults').prepend('<div class="success-message">' + response.message + '</div>');
            } else {
                $('#searchResults').prepend('<div class="error-message">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#searchResults').prepend('<div class="error-message">Произошла ошибка при сохранении книги</div>');
        }
    });
}

$('#searchQuery').on('keypress', function(e) {
    if (e.which === 13) {
        searchBooks();
    }
});