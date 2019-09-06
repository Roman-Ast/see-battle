$(document).ready(function() {
  const canvas = document.querySelector('#canvas');
  const ctx = canvas.getContext('2d');

  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvas.width),
    contentType: 'application/json',
    url: '/field'
  }).done(function(total) {
    ctx.fillStyle = 'brown';
    const canvasWidth = canvas.width;
    const width = canvasWidth / 10;
    const height = canvasWidth / 10;

    const field = total['battleField'];
    for (const ship in field) {
      for (let i = 0; i < field[ship].length; i++) {
        ctx.fillRect(
          field[ship][i]['x'] * width,
          field[ship][i]['y'] * width,
          width,
          height
        );
      }
    }
    console.log(total);
  });
});
