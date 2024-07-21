/**
 * Aurora Heatmap Reporter
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

export { AuroraHeatmapReporter };
import MobileDetect from 'mobile-detect';

class AuroraHeatmapReporter {
	readY = 0;
	readPosition = 1;
	readTimerCount = 0;
	maxReadY = 0;
	disabled = false;
	ajax_delay = 0;

	constructor(config) {
		const html = document.documentElement;
		const body = document.body;

		this.config        = config,
		config.reports  = config.reports.split(',');
		config.debug    = !!parseInt(config.debug);

		config.ajax_delay_time = parseInt(config.ajax_delay_time);
		config.ajax_interval   = (parseInt(config.ajax_interval) || 10) * 1000;
		config.ajax_bulk       = parseInt(config.ajax_bulk) || 10;
		if (!Number.isInteger(config.ajax_delay_time)) {
			config.ajax_delay_time = 3000;
		}
		this.ajax_delay = Date.now() + config.ajax_delay_time;

		// const callback_click     = self.push_click.bind( self );
		// const callback_breakaway = self.push_breakaway.bind( self );

		const md = new MobileDetect(window.navigator.userAgent);

		config.access = md.mobile() ? 'mobile' : md.tablet() ? 'tablet' : 'pc';

		switch (config.access) {
			case 'mobile':
				this.readPosition = 0.1; // 10% of the window.
				window.addEventListener('pagehide', (ev) => this.push_breakaway(ev));
				Array.prototype.forEach.call(
					document.body.children,
					(e) => e.addEventListener('click', (ev) => this.push_click(ev))
				);
				break;
			case 'pc':
				this.readPosition = 0.5; // Center of the screen.
				window.addEventListener( 'beforeunload', (ev) => this.push_breakaway(ev) );
				document.addEventListener('click', (ev) => this.push_click(ev))
				break;
			case 'tablet':
			default:
				return;
		}

		/**
		 * Hook Calculate reading area
		 */
		window.setInterval(() => this.calc_attention(), 1000);
	}

	/**
	 * Get current readY
	 */
	getReadY() {
		return Math.floor(this.getScrollTop() + this.getWindowHeight() * this.readPosition);
	}

	/**
	 * Calcurate attention area
	 */
	calc_attention() {
		const readY   = this.getReadY();
		this.maxReadY = Math.max(this.maxReadY, readY);
		if (readY === this.readY) {
			this.readTimerCount++;
		} else {
			this.readTimerCount = 0;
		}
		this.readY = readY;
		if (3 === this.readTimerCount) {
			this.push_attention();
		}
	}

	/**
	 * Get Mouse Cursor Position
	 *
	 * @param Event ev
	 * @return Object
	 */
	getCursorPos(ev) {
		const html = document.documentElement;
		const body = document.body;
		if ((ev.clientX || ev.clientY) && body.scrollLeft) {
			return {
				x: ev.clientX + body.scrollLeft,
				y: ev.clientY + body.scrollTop,
			};
		} else if ((ev.clientX || ev.clientY) && document.compatMode == 'CSS1Compat' && html.scrollLeft) {
			return {
				x: ev.clientX + html.scrollLeft,
				y: ev.clientY + html.scrollTop,
			};
		} else if (ev.pageX || ev.pageY) {
			return {
				x: ev.pageX,
				y: ev.pageY,
			};
		}
	}

	/**
	 * Push click data
	 *
	 * @param Event ev
	 */
	push_click(ev) {
		const pos = this.getCursorPos(ev);
		if (!pos) {
			return;
		}
		pos.event = 'click_' + this.config.access;
		this.push_data(pos, true);
	}

	/**
	 * Get content end
	 */
	getContentEnd() {
		let e = document.getElementsByClassName('ahm-content-end-marker'), y = 0;
		if (e && e.length) {
			e = e[e.length - 1];
			y = this.getPageHeight() - window.pageYOffset - e.getBoundingClientRect().bottom;
			y = Math.max(0, y);
		}
		return y;
	}

	/**
	 * Push breakaway data
	 *
	 * @param Event ev
	 */
	push_breakaway(ev) {
		this.push_data({
			event: 'breakaway_' + this.config.access,
			x: this.getContentEnd(),
			y: Math.max(this.maxReadY, this.getReadY()),
		}, false);
	}

	/**
	 * Push attention data
	 */
	push_attention() {
		this.push_data({
			event: 'attention_' + this.config.access,
			x: this.getContentEnd(),
			y: this.readY,
		}, true);
	}

	/**
	 * Get Scroll Top
	 *
	 * @return Number
	 */
	getScrollTop() {
		const html = document.documentElement;
		const body = document.body;
		return html.scrollTop || body.scrollTop;
	}

	/**
	 * Get Page Width
	 *
	 * @return Number
	 */
	getPageWidth() {
		const html = document.documentElement;
		const body = document.body;
		return html.clientWidth || body.clientWidth || 0;
	}

	/**
	 * Get Page Height
	 *
	 * @return Number
	 */
	getPageHeight() {
		const html = document.documentElement;
		const body = document.body;
		return Math.max(
			body.scrollHeight,
			body.offsetHeight,
			html.clientHeight,
			html.scrollHeight,
			html.offsetHeight
		);
	}

	/**
	 * Get Window Height
	 *
	 * @return Number
	 */
	getWindowHeight() {
		const html = document.documentElement;
		return window.innerHeight || html.clientHeight || 0;
	}

	/**
	 * Temporary stacking data
	 */
	stack = [];

	/**
	 * Build preview HTML
	 *
	 * For debug.
	 *
	 * @param Object e
	 * @return String
	 */
	build_preview(e) {
		return `<div><b>event=</b>${e.event} <b>x=</b> ${('x' in e ? e.x : 'null')} <b>y=</b> ${e.y} <b>height=</b> ${e.height} <b>width=</b> ${e.width}</div>`;
	}

	/**
	 * Show preview HTML
	 *
	 * @param String title
	 * @param Array  content
	 * @param Object style
	 * @param Number timeout
	 */
	show_preview(title, content, style, timeout) {
		const div = document.createElement( 'div' );
		div.setAttribute('style', 'color: #000; padding: 0.2em; position: fixed; right: 0; border: 1px solid #000; font-family: monospace; z-index: 999999;' );
		div.style.background = style.background;
		div.style.top        = style.top;
		div.innerHTML        = `<div style="color: ${style.color}"><b>${title}</b></div>${content.map(this.build_preview).join('')}`;
		document.body.appendChild(div);
		window.setTimeout(() => document.body.removeChild(div), timeout);
	}

	/**
	 * Push data
	 *
	 * @param Object  data
	 * @param Boolean is_async
	 */
	push_data(data, is_async) {
		if (this.disabled) {
			return;
		}

		const now = Date.now();
		let post;

		if (data && ~this.config.reports.indexOf(data.event)) {
			data.time   = now;
			data.width  = this.getPageWidth();
			data.height = this.getPageHeight();
			this.stack.push(data);

			// Debug: display stored data for 1 second.
			if (this.config.debug) {
				this.show_preview('Store', [data], { color: '#963', background: '#ffc', top: '0' }, 1000);
			}
		}

		// Avoid recording such as JavaScript redirects.
		if (now <= this.ajax_delay) {
			return;
		}

		const firstTime = this.stack.reduce((a, e) => Math.min(a, e.time), now);
		// For async, check ajax_interval and ajax_bulk.
		if (is_async && (now - firstTime) < this.config.ajax_interval && this.stack.length < this.config.ajax_bulk) {
			return;
		}

		// Stacked no data, do nothing.
		if (!this.stack.length) {
			return;
		}

		[post, this.stack] = [this.stack, []];
		post.forEach((e) => {
			e.time   = Math.floor((e.time - now) / 1000);
			e.x      = Math.floor(e.x);
			e.y      = Math.floor(e.y);
			e.width  = Math.floor(e.width);
			e.height = Math.floor(e.height);
		});

		// Debug: display sending data for 5 seconds.
		if (this.config.debug) {
			this.show_preview('Send', post, { color: '#369', background: '#cff', top: '4em' }, 5000);
		}

		const form = new FormData();
		form.append('action', this.config.action);
		form.append('url', document.location.href);
		form.append('title', document.title);
		post.forEach((e, i) => {
			Object.keys(e).forEach((key) => {
				form.append(`data[${i}][${key}]`, e[key]);
			});
		});

		if (navigator.sendBeacon) {
			if (navigator.sendBeacon(this.config.ajax_url, form)) {
				return;
			}
		}

		fetch(this.config.ajax_url, {
			method: 'POST',
			body: form,
			mode: 'same-origin',
			cache: 'no-cache',
			keepalive: is_async,
		}).catch((e) => {
			console.error(e);
		});
	}
}

/* vim: set ts=4 sw=4 sts=4 noet: */
