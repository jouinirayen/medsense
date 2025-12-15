<!-- Siri Floating Bubble Widget -->
<div id="siri-widget" class="siri-container">

    <!-- Glass Backdrop (Click to Activate) -->
    <div class="siri-glass" id="siri-trigger"></div>

    <!-- Liquid Animation Wrapper -->
    <div class="siri-blobs">
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
    </div>

    <!-- Status Text -->
    <div id="siri-status" class="siri-text">Appuyez pour parler</div>

    <!-- SVG Filter for Gooey Effect -->
    <svg style="position: absolute; width: 0; height: 0;">
        <filter id="goo">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
            <feComposite in="SourceGraphic" in2="goo" operator="atop" />
        </filter>
    </svg>
</div>

<!-- Initial Activation: Widget is visible in Standby -->
<script>
    window.addEventListener('load', () => {
        const widget = document.getElementById('siri-widget');
        widget.classList.add('is-active', 'standby'); // Show immediately
        
        // Browser requires USER INTERACTION to start mic.
        // We turn the whole widget into the trigger.
        document.getElementById('siri-trigger').addEventListener('click', () => {
            if(window.SiriController) window.SiriController.toggle();
        });
    });
</script>