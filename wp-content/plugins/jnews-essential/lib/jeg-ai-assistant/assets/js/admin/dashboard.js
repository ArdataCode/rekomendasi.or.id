;(function ($) {
  $(function () {
    var apiLocationURL = new URL(window.location.href)
    if (apiLocationURL.searchParams.get('action') === 'submit-key' && apiLocationURL.searchParams.get('api_key') !== null) {
      apiLocationURL.searchParams.delete('action')
      apiLocationURL.searchParams.delete('api_key')

      window.history.replaceState(null, null, apiLocationURL)
    }
  })
})(jQuery)
