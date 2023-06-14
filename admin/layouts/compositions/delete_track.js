
function confirmDelete(trackId) {
  track_delete = document.getElementById('admin_track_container_' + trackId);
  track_delete_button = document.getElementById('track_delete_button_' + trackId);
  track_title = track_delete_button.getAttribute('value');
  var result = confirm("Вы уверены, что хотите удалить трек " + track_title + " ?");
  if (result) {
    deleteTrack(trackId, track_delete, track_title);
  }
}

function deleteTrack(trackId, track_delete) {
  admin_success_alert = document.getElementById('admin_success_alert_track');
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "compositions.php");
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log(xhr.responseText);
      admin_success_alert.innerHTML = "Трек " + track_title + " успешно удален!";
      admin_success_alert.style.paddingLeft = "10px";
      admin_success_alert.style.paddingRight = "10px";
      track_delete.remove();
    }
  };
  xhr.send("deletedTrackId=" + trackId);
}