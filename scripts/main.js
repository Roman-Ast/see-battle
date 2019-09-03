$(document).ready(function() {
  const canvas = document.querySelector('#canvas');
  const ctx = canvas.getContext('2d');

  $.ajax({
    type: 'GET',
    data: JSON.stringify(),
    contentType: 'application/json',
    url: '/field'
  }).done(function(data) {
    ctx.fillStyle = 'brown';
    ctx.fillRect(
      data.coordinates.x,
      data.coordinates.y,
      data.width,
      data.height
    );
  });
});
