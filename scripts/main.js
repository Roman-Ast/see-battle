$(document).ready(function() {
  const canvasAI = document.querySelector('#canvasAI');
  const canvasUser = document.querySelector('#canvasUser');
  const ctxAi = canvasAI.getContext('2d');
  const ctxUser = canvasUser.getContext('2d');
  const canvasAIWidth = canvasAI.width;
  const shipWidth = canvasAIWidth / 10;
  const shipHeight = canvasAIWidth / 10;
  const repoOfShips = new Map();
  
  $('#messages').addClass('alert-primary');
  $('#messages').html(
    `<h4>Добро пожаловать, боец!</h4>
    <p>Расставьте Ваши корабли в соответствии
    с <a href="https://ru.wikipedia.org/wiki/%D0%9C%D0%BE%D1%80%D1%81%D0%BA%D0%BE%D0%B9_%D0%B1%D0%BE%D0%B9_(%D0%B8%D0%B3%D1%80%D0%B0)" target="_blank">
    правилами игры</a>.</p>
    <h5>Желаем удачи!</h5>`
    );

  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvasAI.width),
    contentType: 'application/json',
    url: '/field'
  }).done(function(total) {

    const field = total['aiships'];
    console.log(field);
    /*for (const ship in field) {
      for (let i = 0; i < field[ship].length; i++) {
        ctxAi.fillRect(
          field[ship][i]['x'] * shipWidth,
          field[ship][i]['y'] * shipHeight,
          shipWidth,
          shipHeight
        );
      }
    }*/
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
        $('#shootbtn').attr('disabled', 'disabled');
        $('#aishoot').removeAttr('disabled');
        const img = new Image(30, 30);
        img.onload = function () {
          ctxAi.drawImage(
            img,
            total['miss']['x'] * shipHeight,
            total['miss']['y'] * shipWidth,
            35,
            35
          );
        }
        img.src = "/img/miss.png";
      } else {
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(`<h4>Попадание!</h4>`);
         console.log(total['deletedItem']);
         const img = new Image(30, 30);
         
         img.onload = function () {
           ctxAi.drawImage(
             img,
             total['deletedItem'][0]['x'] * shipHeight,
             total['deletedItem'][0]['y'] * shipWidth,
             35,
             35
           );
         }
         img.src = "/img/boom.png";
        if (!total['isShipAfloat']) {
          console.log(total);
          const nameOfSunkedShip = 'coords' + $('#typeOfShip')
              .find(`option[name=${total['sunkedShip']}]`)
              .val();
          $('#messages').html(
            `<h4>Попадание!</h4>
            <h4>Потоплен корабль ${nameOfSunkedShip}!</h4>`

          );
        }
          
      }
      const newField = total['aishipsUpdated'];
      //ctxAi.clearRect(0, 0, canvasUser.width, canvasUser.height);

      /*for (const ship in newField) {
        for (let i = 0; i < newField[ship].length; i++) {
          ctxAi.fillRect(
            newField[ship][i]['x'] * shipWidth,
            newField[ship][i]['y'] * shipHeight,
            shipWidth,
            shipHeight
          );
        }
      }*/
    });
  });

  const aiShooting = (e, intForProgressBar) => {
    clearInterval(intForProgressBar);
    $('#messages').removeClass('alert-warning');
    $('#messages').addClass('alert-primary');

    $.ajax({
      type: 'POST',
      data: JSON.stringify(),
      contentType: 'application/json',
      url: '/aishooting'
    }).done(function (response) {
      
      const points = response['resultArr'].filter(function (el) {
        if (el) return el;
      });
      
      if (response['resOfShooting'].length > 0) {
        $('#messages').removeClass('alert-primary');
        $('#messages').addClass('alert-danger');
        $('#messages').html(
          `<p>
          Компьютер нанес удар по координатам 
          y: ${response['resOfShooting'][0][0]['y']}
          x: ${response['resOfShooting'][0][0]['x']}</p>
          <h4>Попадание!</h4>`
          );
          if (response['isShipAfloat'] === false) {
            console.log(response.sunkedShip);
            const nameOfSunkedShip = $('#typeOfShip')
              .find(`option[name=${response.sunkedShip}]`)
              .val();

            $('#messages').removeClass('alert-primary');
            $('#messages').addClass('alert-danger');
            $('#messages').html(
              `<p>
              Компьютер нанес удар по координатам 
              y: ${response['resOfShooting'][0][0]['y']}
              x: ${response['resOfShooting'][0][0]['x']}</p>
              <h4>Потоплен корабль ${nameOfSunkedShip}!</h4>`
              );
          }
      } else {
        $('#messages').removeClass('alert-primary');
        $('#messages').removeClass('alert-danger');
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(
          `<p>Компьютер нанес удар по координатам 
          y: ${response['resOfShooting']['y']}
          x: ${response['resOfShooting']['x']}</p>
          </h2>ПРОМАX!</h2>`
          );
          $('#aishoot').attr('disabled', 'true');
          $('#shootbtn').removeAttr('disabled');
      }
      
      ctxUser.clearRect(0, 0, canvasUser.width, canvasUser.height);
      points.forEach(function (ship, i, arr) {
        ship.forEach(function (point, i, points) {
          ctxUser.fillRect(
            point['x'] * shipWidth,
            point['y'] * shipHeight,
            shipWidth,
            shipHeight
          );
        });
      });
    });
  }
  const timer = (e) => {
    e.preventDefault();
    $('#messages').html(`
    <h4>Ход компьютера...</h4>
      <div class="spinner-border text-info" role="status" style="text-align:center;">
        <span class="sr-only">Loading...</span>
      </div>
    `);
    /*$('#messages').html(
      `<h4>Ход компьютера...</h4>
      <div class="progress" style="height: 20px;">
        <div class="progress-bar"
        role="progressbar" aria-valuenow="10" aria-valuemin="0"
        aria-valuemax="100" style="width: 100%"></div>
      </div>`
      );
    let counter = 0;
    let intForProgressBar = setInterval(function () {
      counter += 4;
      $('.progress').css({'width': counter});
    }, 22);*/
    setTimeout(aiShooting, 2000, /*intForProgressBar*/);
  }
  $('#aishoot').on('click', timer);
});
