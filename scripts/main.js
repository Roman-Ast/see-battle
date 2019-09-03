$(document).ready(function() {
  const canvas = document.querySelector('#canvas');
  const ctx = canvas.getContext('2d');

  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvas.width),
    contentType: 'application/json',
    url: '/field'
  }).done(function(data) {
    ctx.fillStyle = 'brown';

    for (let key in data) {
      for (let i = 0; i < data[key].length; i += 1) {
        ctx.fillRect(data[key][i], Number(key), 50, 50);
      }
    }
  });
});
