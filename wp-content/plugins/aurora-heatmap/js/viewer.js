/**
 * Aurora Heatmap Viewer
 *
 * @package aurora-heatmap
 * @copyright 2019-2024 R3098 <info@seous.info>
 * @version 1.7.0
 */

export { AuroraHeatmapViewer };
import h337 from 'heatmap.js';
import chroma from 'chroma-js';

class AuroraHeatmapViewer {
	constructor(config) {
		this.config = config;
		const html  = document.documentElement;
		const body  = document.body;

		config.count_bar = parseInt(config.count_bar);
		config.width     = parseInt(config.width);

		const data = JSON.parse(config.data);
		const div  = this.createHeatmapContainer();
		this.set_viewport();
		if (config.event.endsWith('_pc')) {
			body.style.webkitTextSizeAdjust = '100%';
			body.style.textSizeAdjust       = '100%';
		}
		switch (config.event) {
			case 'click_pc':
			case 'click_mobile':
				return this.drawHeatmap(div, data);
			case 'breakaway_pc':
			case 'breakaway_mobile':
				return this.drawVerticalHeatmap(
					div,
					data,
					chroma.scale(['#848484', '#9A9B6C', '#B0A25A', '#C0A847', '#E29A34', '#FD8D3C']).mode('lab'),
					true
				);
			case 'attention_pc':
			case 'attention_mobile':
				return this.drawVerticalHeatmap(
					div,
					data,
					chroma.scale(['#004046', '#006D72', '#00CED1', '#FFD700', '#FFFF00']).domain([0, 0.06, 0.16, 0.9, 1]).mode('lab'),
					false
				);
		}
	}

	/**
	 * Set viewport for non PC
	 */
	set_viewport() {
		const view_width = this.config.width;
		if ( ! view_width ) {
			return;
		}
		var viewport = document.querySelector('meta[name="viewport"]');
		var content  = `width=${view_width}`;
		if ( viewport ) {
			viewport.content = content;
		} else {
			viewport         = document.createElement('meta');
			viewport.name    = 'viewport';
			viewport.content = content;
			document.head.appendChild(viewport);
		}
	}

	/**
	 * Get page height
	 */
	getPageHeight() {
		return Math.max(
			document.body.scrollHeight,
			document.body.offsetHeight,
			document.documentElement.offsetHeight
		);
	}

	/**
	 * Create heatmap Container
	 *
	 * @return Element
	 */
	createHeatmapContainer() {
		const container       = document.createElement('div');
		const flow            = document.createElement('div');
		container.className   = 'ahm-heatmap-container';
		flow.className        = 'ahm-heatmap-flow';
		let scrollY           = 0;
		container.appendChild(flow);
		document.body.appendChild(container);
		window.addEventListener('scroll', (e) => {
			const s = document.documentElement.scrollTop || document.body.scrollTop;
			if (scrollY !== s) {
				scrollY        = s;
				flow.style.top = (- s - flow.parentElement.getBoundingClientRect().top) + 'px';
			}
		});
		return flow;
	}

	/**
	 * Draw vertical heatmap
	 *
	 * @param Element  div
	 * @param Array    data
	 * @param Function palette
	 * @param Boolean  is_show_ratio
	 */
	drawVerticalHeatmap(div, data, palette, is_show_ratio) {
		if (!data) {
			return;
		}

		/**
		 * Make label
		 *
		 * @param Array data
		 * @return Array
		 */
		function make_label(data) {
			// Convert 0.0 ~ 1.0 to 0 ~ 100 step 10.
			const steps = data.map((e) => Math.floor( e * 10 ) * 10);

			const labels = new Array(data.length);

			// Get changing index.
			steps.reduce((a, c, i) => {
				if (a !== c) {
					labels[i] = steps[i] + '%';
				}
				return c;
			});

			return labels;
		}

		if (is_show_ratio) {
			data.ratios = make_label(data.ratios);
		}

		let color_prev, max_index = data.colors.length - 1;
		div.style.minHeight       = (40 * data.colors.length) + 'px';
		data.colors.forEach((colors, i) => {
			const grad = document.createElement('div');
			let bg;
			// Set background, linear-gradient.
			const color_next = (i < max_index) ? data.colors[i + 1][0] : colors[3] || colors[2] || colors[1] || colors[0];
			if (colors[0] === colors[2] && colors[2] === color_next) {
				bg = palette(color_next).alpha(0.5).css();
			} else {
				bg = 'linear-gradient(to bottom,' + [
					palette(colors[0]).alpha(0.5).css(),
					palette(colors[1] || colors[0]).alpha(0.5).css(),
					palette(colors[2] || colors[1] || colors[0]).alpha(0.5).css(),
					palette(colors[3] || colors[2] || colors[1] || colors[0]).alpha(0.5).css(),
					palette(color_next).alpha(0.5).css(),
				].join( ',' ) + ')';
			}
			grad.setAttribute('style', 'background:' + bg);
			color_prev = color_next;
			// Set ratio label.
			if (is_show_ratio && data.ratios[i]) {
				grad.innerHTML = `<span>${data.ratios[i]}</span>`;
			}
			// Count bar.
			if (this.config.count_bar) {
				grad.innerHTML += `<span class="count-bar">${data.counts[i]}</span>`;
			}
			grad.className = 'height-40px';
			div.appendChild(grad);
		});

		// Automatic stretch element.
		const grad = document.createElement('div');
		grad.setAttribute('style', 'flex: 1 1 auto; width: 100%; background: ' + palette(color_prev).alpha(0.5).css() + ';' );
		div.appendChild(grad);
	}

	/**
	 * Draw heatmap
	 *
	 * @param Element div
	 * @param Array   data
	 */
	drawHeatmap(div, data) {
		// Count bar.
		if (this.config.count_bar) {
			data.counts.forEach((e, i) => {
				const s     = document.createElement('div');
				s.className = 'count-bar';
				s.style.top = (40 * i) + 'px';
				s.innerText = e;
				div.appendChild(s);
			});
		}
		const max_height = Math.ceil(data.points.reduce((prev, curr) => Math.max( curr.y, prev ), 0) / 4000);

		// Draw heatmap on canvas every 4000px.
		for (var i = 0; i <= max_height; i++) {
			const id  = 'heatmapCanvas' + i;
			const cv  = document.createElement( 'div' );
			const top = i * 4000;
			cv.setAttribute('id', id);
			cv.style.width  = this.config.width + 'px';
			cv.style.height = '4000px';
			div.appendChild(cv);
			var heatmap = h337.create({
				container: cv,
				maxOpacity: .6,
				radius: 50,
				blur: .9,
				backgroundColor: 'transparent',
			});
			cv.style.top = '0px';
			heatmap.setData({
				min: 0,
				max: 5,
				data: data.points.map((e) => {
					return {
						x: e.x,
						y: e.y - top,
						value: 1
					};
				})
			});
		}
	}
}

/* vim: set ts=4 sw=4 sts=4 noet: */
