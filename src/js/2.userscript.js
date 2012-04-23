$(function () {
  var show = false;
  $('#logo img').on('click', function () {
    if (show) {
      $('#content').slideUp(240);
    } else {
      $('#content').slideDown(720);
    }
    show = !show;
  });

  var sort = { fqdn:true, query:true, times:true, date:true, last:false }
  var adjs = function (exc) {
    sort.fqdn  = (exc != 'fqdn' ) ? false : !sort.fqdn;
    sort.query = (exc != 'query') ? false : !sort.query;
    sort.times = (exc != 'times') ? false : !sort.times;
    sort.date  = (exc != 'date' ) ? false : !sort.date;
    sort.last  = (exc != 'last' ) ? false : !sort.last;
  }
  $('#content > ul > .row').on('click', function () {
    switch(true) {
      case $(this).hasClass('fqdn') :
        adjs('fqdn');
        $('.sort').sortElements(function(a,b) {
          return sort.fqdn
            ? ($(a).attr('x-fqdn') < $(b).attr('x-fqdn') ? 1 : -1)
            : ($(a).attr('x-fqdn') > $(b).attr('x-fqdn') ? 1 : -1);
        });
        break;
      case $(this).hasClass('query') :
        adjs('query');
        $('.sort').sortElements(function(a,b) {
          return sort.query
            ? ($(a).attr('x-query') < $(b).attr('x-query') ? 1 : -1)
            : ($(a).attr('x-query') > $(b).attr('x-query') ? 1 : -1);
        });
        break;
      case $(this).hasClass('times') :
        adjs('times');
        $('.sort').sortElements(function(a,b) {
          return sort.times
            ? (parseInt($(a).attr('x-times')) < parseInt($(b).attr('x-times')) ? 1 : -1)
            : (parseInt($(a).attr('x-times')) > parseInt($(b).attr('x-times')) ? 1 : -1);
        });
        break;
      case $(this).hasClass('date') :
        adjs('date');
        $('.sort').sortElements(function(a,b) {
          return sort.date
            ? ($(a).attr('x-date') < $(b).attr('x-date') ? 1 : -1)
            : ($(a).attr('x-date') > $(b).attr('x-date') ? 1 : -1);
        });
        break;
      case $(this).hasClass('last') :
        adjs('last');
        $('.sort').sortElements(function(a,b) {
          return sort.last
            ? ($(a).attr('x-last') < $(b).attr('x-last') ? 1 : -1)
            : ($(a).attr('x-last') > $(b).attr('x-last') ? 1 : -1);
        });
        break;
    }
  });
});
