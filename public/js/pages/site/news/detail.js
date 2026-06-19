  window.copyToClipboard = function () {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
      alert('Đã sao chép liên kết!');
    }).catch(err => {
      console.error('Failed to copy: ', err);
    });
  };
