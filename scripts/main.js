$(document).ready(function() {
  const canvasAI = document.querySelector('#canvasAI');
  const canvasUser = document.querySelector('#canvasUser');
  const ctxAi = canvasAI.getContext('2d');
  const ctxUser = canvasUser.getContext('2d');
  const canvasAIWidth = canvasAI.width;
  const shipWidth = canvasAIWidth / 10;
  const shipHeight = canvasAIWidth / 10;
  const repoOfShips = new Map();
  
  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvasAI.width),
    contentType: 'application/json',
    url: '/field'
  }).done(function(total) {
    ctxAi.fillStyle = 'brown';

    const field = total['aiships'];
    
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

    ctxUser.clearRect(0, 0, canvasUser.width, canvasUser.height);

    const shipType = $('#typeOfShip')
      .find('option:selected')
      .attr('name');
    const children = $(`#${shipType}`)
      .children()
      .children('input');
    const coordsArr = [];
    for (const input in children) {
      if (children[input].value) {
        if (children[input].value.match(/^\d+$/)) {
          coordsArr.push(children[input].value);
          $('#messages').html('');
          $('#messages').removeClass('alert-danger');
          $(children[input]).css({ border: '1px solid #bbb' });
        } else {
          $('#messages').html(`<h4>Вы ввели недопустимые координаты</h4>`);
          $('#messages').addClass('alert-danger');
          $(children[input]).css({ border: '1px solid red' });
        }
      }
    }
    const readyShipCoords = coordsArr.reduce((acc, el, index, arr) => {
      return index % 2 === 0 ? acc.concat({ y: el, x: arr[index + 1] }) : acc;
    }, []);

    repoOfShips.set(shipType, readyShipCoords);

    for (let points of repoOfShips.values()) {
      for (const point of points) {
        ctxUser.fillRect(
          point.x * shipWidth,
          point.y * shipHeight,
          shipWidth,
          shipHeight
        );
      }
    }

    if (repoOfShips.size >= 2) {
      $('#sendShips').removeAttr('disabled');
    }
  });

  $('#sendShips').on('click', function(e) {
    e.preventDefault();

    const arrForSend = {};

    repoOfShips.forEach( (value, key, map) => {
      arrForSend[key] = value;
    });

    $.ajax({
      type: 'POST',
      data: JSON.stringify(arrForSend),
      contentType: 'application/json',
      url: '/createUserShips'
    }).done(function(response) {
      console.log(response);
      const shipsWithErrors = [];
      if (response.error) {
        repoOfShips.forEach((value, key) => {
          for (let i = 0; i < value.length; i++) {
            if (
              response.coords[0]['y'] === value[i]['y'] &&
              response.coords[0]['x'] === value[i]['x']
            ) {
              shipsWithErrors.push(key);
            }
          }
        });
        let str = '';
        for (let i = 0; i < shipsWithErrors.length; i++) {
          str +=
            '<li>' +
            $('#typeOfShip')
              .find(`option[name=${shipsWithErrors[i]}]`)
              .val() +
            '</li>';
        }
        $('#messages').html(
          `<h5>Возможные проблемы</h5> 
          <span>Hеверные координаты в кораблях:</span>
          <ul>${str}<ul>`
        );
        $('#messages').addClass('alert-danger');
        $('#messages').css({ overflow: 'scroll' });
        console.log(shipsWithErrors);
      }
      if (response === 'Ok') {
        $('#messages').html(`<h4>Все готово к началу игры!</h4>`);
        $('#messages').removeClass('alert-danger');
        $('#messages').addClass('alert-success');
        $('#messages').css({ overflow: 'hidden' });

        $('#sendShips').css({'display': 'none'});
        $('#Userform').css({'display': 'none'});
        $('#shoot').css({'display': 'flex'});
      }
    });
  });

  $('#shootbtn').on('click', function(e) {
    e.preventDefault();

    const y = $('#targetY').val();
    const x = $('#targetX').val();

    $.ajax({
      type: 'POST',
      data: JSON.stringify({
        y,
        x
      }),
      contentType: 'application/json',
      url: '/usershooting'
    }).done(function(total) {
      if (!total['deletedItem']) {
        $('#messages').removeClass('alert-success');
        $('#messages').addClass('alert-warning');
        $('#messages').html(`<h4>Мимо!</h4>`);
        $('#next').css({'display':'block'});
      } else {
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(`<h4>Попадание!</h4>`);
         console.log(total['deletedItem']);
         const img = new Image();
         img.src = "/../img/boom.png";
          ctxAi.drawImage(
            img,
            total['deletedItem']['x'] * shipWidth,
            total['deletedItem']['y']  * shipHeight
          );
          
          
      }
      const newField = total['aishipsUpdated'];
      ctxAi.clearRect(0, 0, canvasUser.width, canvasUser.height);

      for (const ship in newField) {
        for (let i = 0; i < newField[ship].length; i++) {
          ctxAi.fillRect(
            newField[ship][i]['x'] * shipWidth,
            newField[ship][i]['y'] * shipHeight,
            shipWidth,
            shipHeight
          );
        }
      }
    });
  });

  $('#aishoot').on('click', function (e) {
    e.preventDefault();

    $('#messages').removeClass('alert-warning');
    $('#messages').addClass('alert-primary');
    $('#messages').html(`<h4>Ход компьютера...</h4>`);

    $.ajax({
      type: 'POST',
      data: JSON.stringify(),
      contentType: 'application/json',
      url: '/aishooting'
    }).done(function (response) {
      console.log(response);
      const points = response['resultArr'].filter(function (el) {
        if (el) return el;
      });

      if (response['resOfShooting']) {
        $('#messages').removeClass('alert-primary');
        $('#messages').addClass('alert-danger');
        $('#messages').html(
          `<h4>
          Компьютер нанес удар по координатам 
          y: ${response['resOfShooting'][0][0]['y']}
          x: ${response['resOfShooting'][0][0]['x']},
          попадание!
          </h4>`
          );
      } else {
        $('#messages').removeClass('alert-primary');
        $('#messages').removeClass('alert-danger');
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(
          `<h4>
          Компьютер нанес удар по координатам 
          y: ${response['resOfShooting'][0][0]['y']}
          x: ${response['resOfShooting'][0][0]['x']},
          и ПРОМАЗАЛ!
          </h4>`
          );
      }

      ctxUser.clearRect(0, 0, canvasUser.width, canvasUser.height);
      points.forEach(function (ship, i, arr) {
        if(ship) {
          ctxUser.fillRect(
            ship[i]['x'] * shipWidth,
            ship[i]['y'] * shipHeight,
            shipWidth,
            shipHeight
          );
        }
        
      });
    });
  });
});
