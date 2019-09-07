$(document).ready(function() {
  const canvasAI = document.querySelector('#canvasAI');
  const canvasUser = document.querySelector('#canvasUser');
  const ctxAi = canvasAI.getContext('2d');
  const ctxUser = canvasUser.getContext('2d');
  const canvasAIWidth = canvasAI.width;
  const shipWidth = canvasAIWidth / 10;
  const shipHeight = canvasAIWidth / 10;

  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvasAI.width),
    contentType: 'application/json',
    url: '/field'
  }).done(function(total) {
    ctxAi.fillStyle = 'brown';

    const field = total['battleField'];
    for (const ship in field) {
      for (let i = 0; i < field[ship].length; i++) {
        ctxAi.fillRect(
          field[ship][i]['x'] * shipWidth,
          field[ship][i]['y'] * shipHeight,
          shipWidth,
          shipHeight
        );
      }
    }
  });

  $('#typeOfShip').on('change', function() {
    const shipType = $(this)
      .find('option:selected')
      .attr('name');
    $('.coords').css({ display: 'none' });
    $(`#${shipType}`).css({ display: 'flex' });
  });

  $('.submit').on('click', function(e) {
    e.preventDefault();

    const shipType = $('#typeOfShip')
      .find('option:selected')
      .attr('name');
    const children = $(`#${shipType}`)
      .children()
      .children('input');
    const coordsArr = [];
    for (const input in children) {
      if (children[input].value) coordsArr.push(children[input].value);
    }
    const readyShipCoords = coordsArr.reduce((acc, el, index, arr) => {
      return index % 2 === 0 ? acc.concat({ y: el, x: arr[index + 1] }) : acc;
    }, []);

    console.log(readyShipCoords);
    /*ships.forEach((value, key, map) => {
      value.forEach(el => {
        ctxUser.fillRect(
          el['x'] * shipWidth,
          el['y'] * shipHeight,
          shipWidth,
          shipHeight
        );
      });
    });*/
  });
});
