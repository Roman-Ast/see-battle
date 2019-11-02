$(document).ready(function() {
  const canvasAI = document.querySelector('#canvasAI');
  const canvasUser = document.querySelector('#canvasUser');
  const ctxAi = canvasAI.getContext('2d');
  const ctxUser = canvasUser.getContext('2d');
  const canvasAIWidth = canvasAI.width;
  const shipWidth = canvasAIWidth / 10;
  const shipHeight = canvasAIWidth / 10;
  const repoOfShips = new Map();

  $('#userShoot').attr('disabled', 'true');
  $('.postShip').attr('disabled', 'true');
  $('#sendShips').removeAttr('disabled');
  $('#messages').addClass('alert-primary');
  $('#messages').html(
    `<h4>Добро пожаловать, боец!</h4>
    <p>Расставьте Ваши корабли в соответствии
    с <a href="https://ru.wikipedia.org/wiki/%D0%9C%D0%BE%D1%80%D1%81%D0%BA%D0%BE%D0%B9_%D0%B1%D0%BE%D0%B9_(%D0%B8%D0%B3%D1%80%D0%B0)" target="_blank">
    правилами игры</a>.</p>
    <h5>Желаем удачи!</h5>`
    );


  const isUserSpecifiesCoordsValid = (shipType) => {
    const children = $(`#${shipType}`).children();
    const arrValue = [];
    const arrBorderColor = [];

    for (let i = 1; i < children.length - 1; i += 1) {
      const points = $(children[i]).children();
      for (let k = 1; k < points.length; k++) {
        arrValue.push(points[k].value);
        const style = getComputedStyle(points[k]);
        const borderColor = style.getPropertyValue('border-color');
        arrBorderColor.push(borderColor);
      }
    }

    const isEveryNotEmpty = arrValue.every(el => el !== '');
    const isEveryValid = arrBorderColor.every(el => el !== 'rgb(255, 0, 0)');
  
    return isEveryNotEmpty && isEveryValid;
  };

  const isValidShootingUserCoords = () => {
    const objX = document.querySelector('#targetX');
    const objY = document.querySelector('#targetY');
    const styleX = getComputedStyle(objX);
    const styleY = getComputedStyle(objY);
    const borderX = styleX.getPropertyValue('border-color');
    const borderY = styleY.getPropertyValue('border-color');
    const valueX = objX.value;
    const valueY = objY.value;

    return borderX !== 'rgb(255, 0, 0)' && borderY !== 'rgb(255, 0, 0)'
      && valueX !== '' && valueY !== '';
  } ;

  $.ajax({
    type: 'GET',
    data: JSON.stringify(canvasAI.width),
    contentType: 'application/json',
    url: '/getAiShips'
  }).done(response => {
  });
  
  $('#typeOfShip').on('change', function() {
    const shipType = $(this)
      .find('option:selected')
      .attr('name');
    $('.coords').css({ display: 'none' });
    $(`#${shipType}`).css({ display: 'flex' });
  });

  $('.x').on('input', function(e) {
    e.preventDefault();
    
    if (
      !this.value.length ||
      this.value === '0' ||
      this.value.length > 2 ||
      !this.value.match(/^[0-9]+$/) ||
      this.value > 10
      ) {
      $('#messages').addClass('alert-warning');
      $('#messages').html(
        `<h4>Неверные координаты:</h1>
        <p>укажите координаты в диапозоне от 1 до 10</p>
        `
        );
        $(this).addClass('alertXorY');
    } else {
      $('#messages').removeClass('alert-warning');
      $('#messages').addClass('alert-primary');
      $('#messages').html(`<h4>Продолжайте!</h1>`);
      $(this).removeClass('alertXorY');
    }
    
    const shipType = $('#typeOfShip')
      .find('option:selected')
      .attr('name');
    
      isUserSpecifiesCoordsValid(shipType)
      ? $('.postShip').removeAttr('disabled')
      : $('.postShip').attr('disabled', 'true');
  });

  $('.y').on('input', function(e) {
    e.preventDefault();

    if (this.value.length !== 1 || !this.value.match(/^[a-j]+$/i)) {
      $('#messages').addClass('alert-warning');
      $('#messages').html(
        `<h4>Неверные координаты:</h1>
        <p>укажите координаты в диапозоне от А до J</p>
        `
        );
        $(this).addClass('alertXorY');
    } else {
      $('#messages').removeClass('alert-warning');
      $('#messages').addClass('alert-primary');
      $('#messages').html(`<h4>Продолжайте!</h1>`);
      $(this).removeClass('alertXorY');
    }

    const shipType = $('#typeOfShip')
      .find('option:selected')
      .attr('name');
    
      isUserSpecifiesCoordsValid(shipType)
      ? $('.postShip').removeAttr('disabled')
      : $('.postShip').attr('disabled', 'true');
  });

  $('#targetX').on('input', function (e) {
    e.preventDefault();
    
    if (this.value.length !== 1 || !this.value.match(/^[a-j]+$/i)) {
      $('#messages').addClass('alert-warning');
      $('#messages').html(
        `<h4>Проверьте координаты!</h4>`
      );
      $(this).addClass('alertXorY');
    } else {
      $('#messages').removeClass('alert-warning');
      $('#messages').html(
        `<h4>Продолжайте!</h4>`
      );
      $('#messages').addClass('alert-success');
      $(this).removeClass('alertXorY');
    }
    
    isValidShootingUserCoords() ? $('#userShoot').removeAttr('disabled')
      : $('#userShoot').attr('disabled', 'true');
  })

  $('#targetY').on('input', function (e) {
    e.preventDefault();

    if (
      !this.value.length ||
      this.value === '0' ||
      this.value.length > 2 ||
      !this.value.match(/^[0-9]+$/) ||
      this.value > 10
    ) {
      $('#messages').addClass('alert-warning');
      $('#messages').html(
        `<h4>Проверьте координаты!</h4>`
      );
      $(this).addClass('alertXorY');
    } else {
      $('#messages').removeClass('alert-warning');
      $('#messages').addClass('alert-success');
      $('#messages').html(
        `<h4>Продолжайте!</h4>`
      );
      $(this).removeClass('alertXorY');
    }

    isValidShootingUserCoords() ? $('#userShoot').removeAttr('disabled')
      : $('#userShoot').attr('disabled', 'true');
  })

  $('.postShip').on('click', function(e) {
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
        coordsArr.push(children[input].value);
      }
    }

    const letters = {
      'a': 0, 'b': 1, 'c': 2, 'd': 3, 'e': 4,
      'f': 5, 'g': 6, 'h': 7, 'i': 8, 'j': 9
    };

    const readyShipCoords = coordsArr.reduce((acc, el, index, arr) => {
      return index % 2 === 0 ? acc.concat({ x: letters[el] , y: arr[index + 1] - 1}) : acc;
    }, []);
    
    repoOfShips.set(shipType, readyShipCoords);
    
    for (let points of repoOfShips.values()) {
      for (const point of points) {
        ctxUser.fillStyle = 'brown';
        ctxUser.fillRect(
          point.x * shipWidth,
          point.y * shipHeight,
          shipWidth,
          shipHeight
        );
      }
    }
    
    (repoOfShips.size >= 0) ? $('#sendShips').removeAttr('disabled'): null;
  });

  $('#sendShips').on('click', function(e) {
    e.preventDefault();

    const arrForSend = {};

    repoOfShips.forEach( (value, key) => {
      arrForSend[key] = value;
    });

    $.ajax({
      type: 'POST',
      data: JSON.stringify(arrForSend),
      contentType: 'application/json',
      url: '/createUserShips'
    }).done(function(response) {
      //console.log(response);
      const rawShipsWithErrors = [];

      if (response.error) {
        repoOfShips.forEach((value, key) => {
          for (let i = 0; i < value.length; i++) {
            if (
              response.coords[0]['y'] === value[i]['y'] &&
              response.coords[0]['x'] === value[i]['x']
            ) {
              rawShipsWithErrors.push(key);
            }
          }
        }); 
        shipsWithErrors = rawShipsWithErrors.reduce((acc, el) =>
          acc.indexOf(el) !== -1 ? acc : acc.concat(el), []);

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
      } else {
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

  $('#userShoot').on('click', function(e) {
    e.preventDefault();

    const letters = {
      'a': 0, 'b': 1, 'c': 2, 'd': 3, 'e': 4,
      'f': 5, 'g': 6, 'h': 7, 'i': 8, 'j': 9
    };

    const y = $('#targetY').val() - 1;
    const x = letters[$('#targetX').val().toLowerCase()];

    $.ajax({
      type: 'POST',
      data: JSON.stringify({ y, x }),
      contentType: 'application/json',
      url: '/userShoot'
    }).done(function(response) {
      //console.log(response);
      if (response.repeat) {
        $('#myModal').fadeIn(200);
        $('.modal-body').html('<h4>Вы уже стреляли в этот квадрат!</h4>');
        $('.modal-header').html('<h3>Предупреждение!</h3>');
        $('#finish').text('close');
        $('#finish').on('click', function(e) {
          e.preventDefault();
          $('#myModal').fadeOut(200);
        });
        return;
      }
      if (!response['deletedItem']) {

        $('#userShoot').attr('disabled', 'true');
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
            response['miss']['x'] * shipHeight,
            response['miss']['y'] * shipWidth,
            35,
            35
          );
        }
        img.src = "../public/img/dot.png";

        setTimeout(() => {
          $('#aishoot').click();
        }, 1000);
      } else {
        $('#userShoot').removeAttr('disabled');
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(`
        <h4>Попадание!</h4>
        <p>Ваш ход</p>
        `);
         
         const img = new Image(30, 30);
         
         img.onload = function () {
           ctxAi.drawImage(
             img,
             response['deletedItem'][0]['x'] * shipHeight,
             response['deletedItem'][0]['y'] * shipWidth,
             35,
             35
           );
         }
         img.src = "../public/img/yepp.png";
        if (!response['isShipAfloat']) {
          const nameOfSunkedShip = $('#typeOfShip')
              .find(`option[name=${response['sunkedShip']}]`)
              .val();
          $('#messages').html(
            `<h6>Попадание!</h6>
            <h4>Потоплен корабль ${nameOfSunkedShip}!</h4>
            <p>Ваш ход!</p>`
          );
        }

        if (response.isWinner) {
          $('#myModal').fadeIn(200);
          $('body').css({'background': 'rgba(33,33,33,.7)'});
          $('.modal-body').html('<h4>Вы победили!</h4>');
          $('.modal-header').html('<h3>Поздравляем!</h3>');
          $('#finish').on('click', function() {
            e.preventDefault();
            $(location).attr('href', '/');
          });
        }
      }
    });
  });

  const aiShooting = () => {
    $('#targetY').val('');
    $('#targetX').val('');
    $('#messages').removeClass('alert-warning');
    $('#messages').addClass('alert-primary');

    $.ajax({
      type: 'POST',
      data: JSON.stringify(),
      contentType: 'application/json',
      url: '/aishooting'
    }).done(function (response) {
      //console.log(response);
      const shipsAfloat = Object.values(response['resultArr']).filter(el => el);
      $('.user_state').html(`Осталось кораблей:${shipsAfloat.length}`);

      const letters = {
        '0': 'A', '1': 'B', '2': 'C', '3': 'D', '4': 'E',
        '5': 'F', '6': 'G', '7': 'H', '8': 'I', '9': 'J'
      };

      const points = [];
      for (const point in response['resultArr']) {
        if (response['resultArr'][point]) {
          points.push(response['resultArr'][point]);
        }
      }
      
      /*ctxUser.clearRect(0, 0, canvasUser.width, canvasUser.height);
      points.forEach(function (point, i, arr) {
        point.forEach(function (ship, i, arr) {
          ctxUser.fillRect(
            ship['x'] * shipWidth,
            ship['y'] * shipHeight,
            shipWidth,
            shipHeight
          );
        })   
      });*/

      if (response['resOfShooting'].length > 0) {
        $('#userShoot').attr('disabled', 'true');
        $('#messages').removeClass('alert-primary');
        $('#messages').addClass('alert-danger');
        $('#messages').html(
          `<p>
          Компьютер нанес удар по координатам 
          ${letters[response['resOfShooting'][0][0]['x']]}
          ${Number(response['resOfShooting'][0][0]['y']) + 1}</p>
            <h4>Попадание!</h4>
            <p>...xод компьютера</p>`
          );

          if (response['isShipAfloat'] === false) {
            
            const nameOfSunkedShip = $('#typeOfShip')
              .find(`option[name=${response.sunkedShip}]`)
              .val();

            $('#messages').removeClass('alert-primary');
            $('#messages').addClass('alert-danger');
            $('#messages').html(
              `<p>
              Компьютер нанес удар по координатам 
              ${letters[response['resOfShooting'][0][0]['x']]}
              ${Number(response['resOfShooting'][0][0]['y']) + 1}</p>
              <h5>Потоплен корабль ${nameOfSunkedShip}!</h5>
              <p>Ход компьютера</p>`
              );
          }
          
          const img = new Image(30, 30);
         
          img.onload = function () {
            ctxUser.drawImage(
              img,
              response['resOfShooting'][0][0]['x'] * shipHeight,
              response['resOfShooting'][0][0]['y'] * shipWidth,
              35,
              35
            );
          }
          img.src = "../public/img/yepp.png";

          setTimeout(() => {
            $('#aishoot').click();
          }, 2000);
      } else {
        $('#messages').removeClass('alert-primary');
        $('#messages').removeClass('alert-danger');
        $('#messages').removeClass('alert-warning');
        $('#messages').addClass('alert-success');
        $('#messages').html(
          `<p>Компьютер нанес удар по координатам 
          ${letters[response['resOfShooting']['x']]}
          ${Number(response['resOfShooting']['y']) + 1}</p>
          </h2>ПРОМАX!</h2>
          <p>Ваш ход!</p>`
          );
          $('#aishoot').attr('disabled', 'true');
          $('#shootbtn').removeAttr('disabled');

          const img = new Image(30, 30);
         
          img.onload = function () {
            ctxUser.drawImage(
              img,
              response['resOfShooting']['x'] * shipHeight,
              response['resOfShooting']['y'] * shipWidth,
              35,
              35
            );
          }
          img.src = "../public/img/dot.png";
      }
      if (response.isWinner) {
        $('#myModal').slideDown(800);
        $('.modal-body').html('<h3>К сожалению, компьютер победил...</h3>');
        $('.modal-header').html('<h3>Миссия провалена!</h3>');
        $('#finish').on('click', function() {
          $(location).attr('href', '/');
        });
        
        const restOfShips = response['isWinner']['restOfShips'];
        ctxAi.fillStyle = 'red';

        for (const ship in restOfShips) {
          const shipCoords = restOfShips[ship];
          for (const point of shipCoords) {
            ctxAi.fillRect(
              point['x'] * shipWidth,
              point['y'] * shipHeight,
              shipWidth,
              shipHeight
            );
          }
        }

        return;
      }
    });
  }
  const timer = (e) => {
    e.preventDefault();

    $('#targetX').val('');
    $('#targetY').val('');
    $('#messages').html(`
    <h4>Ход компьютера...</h4>
      <div class="spinner-border text-info" role="status" style="text-align:center;">
        <span class="sr-only">Loading...</span>
      </div>
    `);
    setTimeout(aiShooting, 2000);
  }
  $('#aishoot').on('click', timer);

  //this functionality is convenient to use when debugging, filling in the user field in one click

  $('#fillTheUserField').on('click', function (e) {
    e.preventDefault();

    repoOfShips.set('oneDeck1', [{'y': 0, 'x': 9}]);
    repoOfShips.set('oneDeck2', [{'y': 0, 'x': 0}]);
    repoOfShips.set('oneDeck3', [{'y': 5, 'x': 6}]);
    repoOfShips.set('oneDeck4', [{'y': 4, 'x': 9}]);
    repoOfShips.set('fourDeck', [
      {'y': 2, 'x': 0},
      {'y': 3, 'x': 0},
      {'y': 4, 'x': 0},
      {'y': 5, 'x': 0}
    ]);
    repoOfShips.set('threeDeck1', [
      {'y': 2, 'x': 3},
      {'y': 2, 'x': 4},
      {'y': 2, 'x': 5}
    ]);
    repoOfShips.set('threeDeck2', [
      {'y': 8, 'x': 1},
      {'y': 8, 'x': 2},
      {'y': 8, 'x': 3}
    ]);
    repoOfShips.set('twoDeck1', [
      {'y': 0, 'x': 4},
      {'y': 0, 'x': 5}
    ]);
    repoOfShips.set('twoDeck2', [
      {'y': 9, 'x': 7},
      {'y': 9, 'x': 8}
    ]);
    repoOfShips.set('twoDeck3', [
      {'y': 5, 'x': 3},
      {'y': 6, 'x': 3}
    ]);

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

    $.ajax({
      type: 'POST',
      data: JSON.stringify(repoOfShips.values()),
      contentType: 'application/json',
      url: '/createUserShips'
    }).done(function (response) {});
  });
});
