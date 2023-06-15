
function confirmDeleteGenre(genreId) {
  genre_delete = document.getElementById('admin_track_container_' + genreId);
  genre_delete_button = document.getElementById('genre_delete_button_' + genreId);
  genre_name = genre_delete_button.getAttribute('value');
  var result = confirm("Вы уверены, что хотите удалить жанр " + genre_name + " ?");
  if (result) {
    deleteGenre(genreId, genre_delete, genre_name);
  }
}

function deleteGenre(genreId, genre_delete,genre_name) {
  admin_success_alert = document.getElementById('admin_success_alert_genre');
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "genres.php");
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log(xhr.responseText);
      admin_success_alert.innerHTML = "Жанр " + genre_name + " успешно удален!";
      admin_success_alert.style.paddingLeft = "10px";
      admin_success_alert.style.paddingRight = "10px";
      genre_delete.remove();
    }
  };
  xhr.send("deletedGenreId=" + genreId);
}