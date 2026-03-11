class MinimogImageLoading extends HTMLElement {
	get intersecting() {
		return this.hasAttribute( 'intersecting' )
	}

	constructor() {
		super()
		this.img              = this.querySelector( 'img' )
		this.observerCallback = this.observerCallback.bind( this )
		this.loadImage        = this.loadImage.bind( this )
		this.img.onload       = this.onLoad.bind( this )
		if ( this.img.complete ) {
			this.setLoadedStage()
		}
	}

	connectedCallback() {
		if ( 'IntersectionObserver' in window ) {
			this.initIntersectionObserver()
		} else {
			this.loadImage()
		}
	}

	disconnectedCallback() {
		this.disconnectObserver()
	}

	loadImage() {
		this.setAttribute( 'intersecting', 'true' )
	}

	onLoad() {
		this.setLoadedStage();
	}

	setLoadedStage() {
		this.removeAttribute( 'data-image-loading' )
		this.classList.add( 'm-img-loaded' )
	}

	observerCallback( entries, observer ) {
		if ( ! entries[0].isIntersecting ) {
			return;
		}
		observer.unobserve( this );
		this.loadImage()
	}

	initIntersectionObserver() {
		if ( this.observer ) {
			return
		}
		const rootMargin = '10px';
		this.observer    = new IntersectionObserver( this.observerCallback, { rootMargin } )
		this.observer.observe( this )
	}

	disconnectObserver() {
		if ( ! this.observer ) {
			return
		}
		this.observer.disconnect()
		this.observer = null
		delete this.observer
	}
}

class MinimogImage extends MinimogImageLoading {
	constructor() {
		super()
	}

	setLoadedStage() {
		this.removeAttribute( 'data-image-loading' )
		this.classList.add( 'm-img-loaded' )
	}
}

customElements.define( 'm-image', MinimogImage )

class MinimogBackgroundImage extends MinimogImageLoading {
	constructor() {
		super()
	}

	setLoadedStage() {
		this.removeAttribute( 'data-background-loading' )
		this.classList.add( 'm-background-loaded' )
	}
}

customElements.define( 'm-background', MinimogBackgroundImage )

