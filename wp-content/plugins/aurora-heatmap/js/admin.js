/**
 * Aurora Heatmap Admin helper
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

export { AuroraHeatmapAdmin };
import chroma from 'chroma-js';

class AuroraHeatmapAdmin {
	constructor (config) {
		if (!(config.active_tab in this)) {
			throw new Error('Aurora Heatmap: invalid config parameter.');
		}
		this[config.active_tab](config);
	}

	view(config) {
		const desc   = document.getElementById('ahm-description');
		const legend = document.getElementById('ahm-legend');
		const table  = document.getElementsByClassName('wp-list-table')[0];

		if ( ! desc || ! table ) {
			return;
		}

		const events = [
			{ column: 'click', legend: config.click_heatmap, },
			{ column: 'breakaway', legend: config.breakaway_heatmap, },
			{ column: 'attention', legend: config.attention_heatmap, },
		];
		let prev;

		table.addEventListener( 'mousemove', (ev) => {
			let e = document.elementFromPoint( ev.clientX, ev.clientY );

			if ( prev && prev === e ) {
				return;
			}

			prev = e;
			while ( 'TD' !== e.tagName && 'TH' !== e.tagName ) {
				if ( 'TABLE' === e.tagName ) {
					return;
				}
				e = e.parentElement;
				if ( ! e ) {
					return;
				}
			}

			events.some((t) => {
				const column_pc     = `column-${t.column}_pc`;
				const column_mobile = `column-${t.column}_mobile`;
				const desc_class    = `${t.column}-heatmap`;
				if ( ( e.classList.contains( column_pc ) || e.classList.contains( column_mobile ) ) && desc_class !== desc.className ) {
					desc.className   = desc_class;
					legend.innerText = t.legend;
					return true;
				}
			});
		});
		this.set_viewer();
	}

	unread() {
		this.set_viewer();
	}

	set_viewer() {
		let w;
		document.querySelectorAll('.ahm-view').forEach((e) => {
			e.addEventListener('click', (ev) => {
				if ( w && w.outerWidth !== parseInt( e.dataset.width ) ) {
					w.close();
				}
				w = window.open(e.dataset.url, 'Aurora Heatmap Viewer', `scrollbars=yes, resizable=no, location=yes, width=${e.dataset.width}, height=600`);
				ev.preventDefault();
			}, { passive: false });
		});
	}

	settings() {
		const radio_group_disable = (e) => {
			return (ev) => {
				e.form[e.name].forEach((r) => {
					const n           = r.nextElementSibling.children[1];
					const is_disabled = n.classList.contains('disabled') || r.disabled || ! r.checked;

					n.querySelectorAll('.inner-label').forEach((i) => {
						i.style.opacity = is_disabled ? '.6' : '1';
					});

					n.querySelectorAll('input[type="text"]').forEach((i) => {
						i.disabled = is_disabled;
					});
				});
			};
		};

		document.querySelectorAll('.ahm-radio-group').forEach((e) => {
			const el = e.parentElement.querySelectorAll('input[type="text"]');

			if (!el || !el.length) {
				return;
			}

			const f = radio_group_disable(e);
			f();
			e.addEventListener('input', f);
		});

		document.getElementById('ahm-options-form')?.addEventListener('keydown', (ev) => {
			if ( 13 === ev.which ) {
				document.getElementById('ahm-options-save').click();
				ev.preventDefault();
				return false;
			}
		});
	}
}

/* vim: set ts=4 sw=4 sts=4 noet: */
