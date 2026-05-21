(function ($) {
  const updateOrder = () => {
    const order = [];

    $('#pwg-media-list .pwg-media-item').each(function () {
      order.push($(this).data('filename'));
    });

    $('#pwg_order').val(order.join(','));
  };

  $(function () {
    const $list = $('#pwg-media-list');
    const copyButton = document.querySelector('.pwg-copy-shortcode');

    if (copyButton) {
      copyButton.addEventListener('click', async () => {
        const shortcode = copyButton.dataset.copyShortcode || '';
        const feedback = document.querySelector('.pwg-copy-feedback');

        try {
          await navigator.clipboard.writeText(shortcode);
          if (feedback) {
            feedback.textContent = 'Copiato';
          }
        } catch (error) {
          const range = document.createRange();
          const value = document.getElementById('pwg-shortcode-value');

          if (value) {
            range.selectNodeContents(value);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
          }

          if (feedback) {
            feedback.textContent = 'Selezionato';
          }
        }
      });
    }

    if (!$list.length) {
      return;
    }

    const selectAllDelete = document.getElementById('pwg_select_all_delete');
    const deleteCheckboxes = Array.from(document.querySelectorAll('input[name="pwg_delete_files[]"]'));

    if (selectAllDelete && deleteCheckboxes.length) {
      selectAllDelete.addEventListener('change', () => {
        deleteCheckboxes.forEach((checkbox) => {
          checkbox.checked = selectAllDelete.checked;
        });
      });

      deleteCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
          selectAllDelete.checked = deleteCheckboxes.every((item) => item.checked);
          selectAllDelete.indeterminate = !selectAllDelete.checked && deleteCheckboxes.some((item) => item.checked);
        });
      });
    }

    $list.sortable({
      handle: '.pwg-media-handle',
      axis: 'y',
      update: updateOrder
    });

    updateOrder();
  });
})(jQuery);
