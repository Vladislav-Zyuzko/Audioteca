
function confirmDeleteAuthor(authorId) {
  author_delete = document.getElementById('admin_track_container_' + authorId);
  author_delete_button = document.getElementById('author_delete_button_' + authorId);
  author_name = author_delete_button.getAttribute('value');
  var result = confirm("Вы уверены, что хотите удалить исполнителя " + author_name + " ?");
  if (result) {
    deleteAuthor(authorId, author_delete, author_name);
  }
}

function deleteAuthor(authorId, author_delete, author_name) {
  admin_success_alert = document.getElementById('admin_success_alert_author');
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "authors.php");
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log(xhr.responseText);
      admin_success_alert.innerHTML = "Трек " + author_name + " успешно удален!";
      admin_success_alert.style.paddingLeft = "10px";
      admin_success_alert.style.paddingRight = "10px";
      author_delete.remove();
    }
  };
  xhr.send("deletedAuthorId=" + authorId);
}