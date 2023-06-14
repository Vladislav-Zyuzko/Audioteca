const player_song_title = document.querySelector('.player_song_title'),
      player_song_cover_image = document.querySelector('.player_song_cover_image'),

      player_progress_bar = document.querySelector('.player_composition_progress'),
      player_progress_bar_container = document.querySelector('.player_composition_progress_container'),

      player_volume_bar = document.querySelector('.slider'),
      player_volume_bar_container = document.querySelector('.player_volume_bar'),

      player_play_button = document.getElementById('player_play_button'),
      player_volume_button = document.querySelector('.player_volume_button'),

      player_play_icon = document.getElementById('player_play_icon'),
      player_volume_icon = document.getElementById('player_volume_icon'),

      player_mode_button = document.getElementById('player_mode_button'),
      player_mode_icon = document.getElementById('player_mode_icon'),

      max_audio_id = document.querySelectorAll('.track').length

var audio = new Audio();
var volume = 0.0;
var current_audio_id = 1;

player_volume_bar.oninput = function() {
    var l = player_volume_bar.offsetWidth
    var k = (l - 30) / l
    var value = (15 + this.value*k*l)/l*100
    this.style.background = 'linear-gradient(to right, var(--color_basic_orange) 0%, var(--color_basic_orange) ' + value + '%, var(--color_basic_grey) ' + value + '%, var(--color_basic_grey) 100%)'
};

window.onload = loadSong("0")

function loadSong (count) {
    init_event = false
    
    if (count == 0) {
        count = 1
        init_event = true
    }

    title_id = 'track_name_' + count
    cover_id = 'track_logo_' + count
    audio_id = 'audio_track_' + count
    play_icon_id = 'track_play_icon_' + count
    prev_play_icon_id = 'track_play_icon_' + current_audio_id

    current_audio_id = count

    player_song_title.innerHTML = document.getElementById(title_id).innerHTML
    player_song_cover_image.setAttribute('src', document.getElementById(cover_id).getAttribute('src'))

    audio.src = document.getElementById(audio_id).getAttribute('src')
    audio.volume = player_volume_bar.getAttribute('value')
    setVolumeIcon(audio.volume)

    if (!init_event) {
        document.getElementById(prev_play_icon_id).setAttribute('src', '../images/icons/play.png')
        document.getElementById(play_icon_id).setAttribute('src', '../images/icons/stop.png')
        player_play_button.setAttribute('value', 'pause')
        player_play_icon.setAttribute('src', '../images/icons/stop.png')
        audio.play()
    }
}

function change_music_status () {
    play_icon_id = 'track_play_icon_' + current_audio_id
    if (player_play_button.getAttribute('value') == 'pause') {
        player_play_button.setAttribute('value', 'play')
        player_play_icon.setAttribute('src', '../images/icons/play.png')
        document.getElementById(play_icon_id).setAttribute('src', '../images/icons/play.png')
        audio.pause()
    }
    else if (player_play_button.getAttribute('value') == 'play') {
        player_play_button.setAttribute('value', 'pause')
        player_play_icon.setAttribute('src', '../images/icons/stop.png')
        document.getElementById(play_icon_id).setAttribute('src', '../images/icons/stop.png')
        audio.play()
    }
}

function change_volume_status () {
    if (player_volume_button.getAttribute('value') == 'on') {
        player_volume_button.setAttribute('value', 'off')
        player_volume_bar.setAttribute('value', 0.0)
        player_volume_icon.setAttribute('src', '../images/icons/volume_off.png')
        volume = audio.volume
        audio.volume = 0.0
    }
    else if (player_volume_button.getAttribute('value') == 'off') {
        player_volume_button.setAttribute('value', 'on')
        player_volume_bar.setAttribute('value', String(volume))
        setVolumeIcon(volume)
        audio.volume = volume
    }
}

function change_mode_status () {
    if (player_mode_button.getAttribute('value') == 'loop') {
        player_mode_button.setAttribute('value', 'infinity')
        player_mode_icon.setAttribute('src', '../images/icons/infinity.png')
    }
    else if (player_mode_button.getAttribute('value') == 'infinity') {
        player_mode_button.setAttribute('value', 'loop')
        player_mode_icon.setAttribute('src', '../images/icons/loop.png')
    }
}

function updateProgress(e) {
    const {duration, currentTime} = e.srcElement
    player_progress_bar.setAttribute('value', String((currentTime / duration) * 100))
    if (currentTime == duration && player_mode_button.getAttribute('value') == 'infinity') {
        loadSong(current_audio_id)
    }
    if (currentTime == duration && player_mode_button.getAttribute('value') == 'loop') {
        setNextTrack()
    }
}

function setProgress(e) {
    const width = this.clientWidth
    const clickX = e.offsetX
    const duration = audio.duration

    audio.currentTime = (clickX / width) * duration
}

function removeEventListeners() {
    player_volume_bar_container.removeEventListener('mousemove', setVolume)
    player_volume_bar_container.addEventListener('mouseup', removeEventListeners)
}

function setMoveEvent() {
    player_volume_bar_container.addEventListener('mousemove', setVolume)
    player_volume_bar_container.addEventListener('mouseup', removeEventListeners)
}

function setVolume(e) {
    const width = this.clientWidth
    clickX = e.offsetX
    current_volume = clickX / width
    if (current_volume < 0) {
        current_volume = 0
    }
    if (current_volume > 1) {
        current_volume = 1
    }

    player_volume_button.setAttribute('value', 'on')
    player_volume_bar.setAttribute('value', String(current_volume))
    audio.volume = current_volume

    setVolumeIcon(audio.volume)
    volume = audio.volume
}

function setVolumeIcon(volume) {
    if (volume < 0.5 && volume > 0.1) {
        player_volume_icon.setAttribute('src', '../images/icons/volume_down.png')
    }
    else if (volume < 0.1) {
        player_volume_icon.setAttribute('src', '../images/icons/volume_mute.png')
    }
    else {
        player_volume_icon.setAttribute('src', '../images/icons/volume_up.png')
    }
}

function setNextTrack() {
    next_audio_id = current_audio_id + 1
    if (next_audio_id == max_audio_id + 1) {
        next_audio_id = 1
    }
    loadSong(next_audio_id)
}

function setPrevTrack() {
    prev_audio_id = current_audio_id - 1
    if (prev_audio_id == 0) {
        prev_audio_id = max_audio_id
    }
    loadSong(prev_audio_id)
}

audio.addEventListener('timeupdate', updateProgress)

player_progress_bar_container.addEventListener('click', setProgress)

player_volume_bar_container.addEventListener('mousedown', setMoveEvent)

