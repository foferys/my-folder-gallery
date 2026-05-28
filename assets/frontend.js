(() => {
  const ready = (callback) => {
    if (document.readyState === 'loading') {
      window.addEventListener('DOMContentLoaded', callback);
      return;
    }

    callback();
  };

  const buildModalController = (modalEl, modalMedia) => {
    if (window.bootstrap && window.bootstrap.Modal) {
      return new window.bootstrap.Modal(modalEl);
    }

    return {
      show() {
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        modalEl.focus();
      },
      hide() {
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
        modalMedia.replaceChildren();
      }
    };
  };

  const initGallery = (galleryEl) => {
    const dataEl = galleryEl.querySelector('.pwg-data');
    const modalEl = galleryEl.querySelector('.pwg-modal');

    if (!dataEl || !modalEl) {
      return;
    }

    let worksData = [];
    try {
      worksData = JSON.parse(dataEl.textContent || '[]');
    } catch (error) {
      worksData = [];
    }

    if (!worksData.length) {
      return;
    }

    const modalTitle = modalEl.querySelector('.pwg-modal-title');
    const modalCounter = modalEl.querySelector('.pwg-modal-counter');
    const modalCaption = modalEl.querySelector('.pwg-modal-caption');
    const modalMedia = modalEl.querySelector('.pwg-modal-media-wrap');
    const modalDots = modalEl.querySelector('.pwg-modal-dots');
    const prevButton = modalEl.querySelector('.pwg-modal-prev');
    const nextButton = modalEl.querySelector('.pwg-modal-next');
    const closeButton = modalEl.querySelector('.pwg-modal-close');
    const worksModal = buildModalController(modalEl, modalMedia);
    let activeWorkIndex = 0;
    let activeMediaIndex = 0;

    const renderMedia = () => {
      const work = worksData[activeWorkIndex];
      const media = work.media[activeMediaIndex];
      const hasMultipleMedia = work.media.length > 1;

      modalTitle.textContent = work.title;
      modalCaption.textContent = work.description || work.title;
      modalCounter.textContent = `${activeMediaIndex + 1} / ${work.media.length}`;
      modalMedia.replaceChildren();
      modalDots.replaceChildren();

      const mediaEl = media.type === 'video' ? document.createElement('video') : document.createElement('img');
      mediaEl.className = 'works-modal-media pwg-modal-media';

      if (media.type === 'video') {
        mediaEl.controls = true;
        mediaEl.autoplay = true;
        mediaEl.muted = true;
        mediaEl.playsInline = true;
        mediaEl.src = media.url;
      } else {
        mediaEl.src = media.url;
        mediaEl.alt = media.alt || work.title;
      }

      modalMedia.appendChild(mediaEl);
      prevButton.hidden = !hasMultipleMedia;
      nextButton.hidden = !hasMultipleMedia;

      work.media.forEach((item, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = index === activeMediaIndex ? 'is-active' : '';
        dot.setAttribute('aria-label', `Apri media ${index + 1}`);
        dot.addEventListener('click', () => {
          activeMediaIndex = index;
          renderMedia();
        });
        modalDots.appendChild(dot);
      });
    };

    const goToMedia = (direction) => {
      const mediaCount = worksData[activeWorkIndex].media.length;
      activeMediaIndex = (activeMediaIndex + direction + mediaCount) % mediaCount;
      renderMedia();
    };

    galleryEl.querySelectorAll('.pwg-card').forEach((card) => {
      const openWork = () => {
        activeWorkIndex = Number(card.dataset.workIndex);
        activeMediaIndex = 0;
        renderMedia();
        worksModal.show();
      };

      card.addEventListener('click', openWork);
      card.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openWork();
        }
      });
    });

    prevButton.addEventListener('click', () => goToMedia(-1));
    nextButton.addEventListener('click', () => goToMedia(1));
    closeButton.addEventListener('click', () => worksModal.hide());

    modalEl.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        worksModal.hide();
      }

      if (event.key === 'ArrowLeft') {
        goToMedia(-1);
      }

      if (event.key === 'ArrowRight') {
        goToMedia(1);
      }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
      modalMedia.replaceChildren();
    });
  };

  ready(() => {
    document.querySelectorAll('.pwg-gallery').forEach(initGallery);
  });
})();
