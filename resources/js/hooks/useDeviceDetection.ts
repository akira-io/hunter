import { useState, useEffect } from 'react';

interface DeviceInfo {
    isMobile: boolean;
    isTablet: boolean;
    isDesktop: boolean;
    screenWidth: number;
    touchSupported: boolean;
}

export const useDeviceDetection = (): DeviceInfo => {
    const [deviceInfo, setDeviceInfo] = useState<DeviceInfo>({
        isMobile: false,
        isTablet: false,
        isDesktop: true,
        screenWidth: 0,
        touchSupported: false,
    });

    useEffect(() => {
        const detectDevice = () => {
            const screenWidth = window.innerWidth;
            const screenHeight = window.innerHeight;
            const touchSupported = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

            // Check orientation: landscape if width > height
            const isLandscape = screenWidth > screenHeight;

            // Mobile: < 768px
            // Tablet: 768px - 1024px (but landscape tablets behave like desktop)
            // Desktop: > 1024px
            const isMobile = screenWidth < 768;
            const isTablet = screenWidth >= 768 && screenWidth <= 1024 && !isLandscape;
            const isDesktop = screenWidth > 1024 || (screenWidth >= 768 && isLandscape);

            // Additional check for user agent (more reliable for some cases)
            const userAgent = navigator.userAgent.toLowerCase();
            const mobileUserAgents = [
                'android', 'iphone', 'ipad', 'ipod', 'blackberry',
                'windows phone', 'mobile', 'tablet'
            ];

            const userAgentIsMobile = mobileUserAgents.some(agent =>
                userAgent.includes(agent)
            );

            // For tablets in landscape, treat as desktop even if user agent says mobile
            const shouldTreatAsDesktop = isLandscape && screenWidth >= 768;

            setDeviceInfo({
                isMobile: isMobile || (userAgentIsMobile && screenWidth < 768),
                isTablet: (isTablet || (userAgentIsMobile && screenWidth >= 768 && screenWidth <= 1024)) && !shouldTreatAsDesktop,
                isDesktop: isDesktop || shouldTreatAsDesktop,
                screenWidth,
                touchSupported,
            });
        };

        // Initial detection
        detectDevice();

        // Listen for resize events (orientation changes)
        window.addEventListener('resize', detectDevice);
        // Listen for orientation changes specifically
        window.addEventListener('orientationchange', detectDevice);

        return () => {
            window.removeEventListener('resize', detectDevice);
            window.removeEventListener('orientationchange', detectDevice);
        };
    }, []);

    return deviceInfo;
};

// Utility function to check if should use mobile chat
export const shouldUseMobileChat = (): boolean => {
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;
    const userAgent = navigator.userAgent.toLowerCase();
    const touchSupported = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    // Check orientation: landscape if width > height
    const isLandscape = screenWidth > screenHeight;

    const mobileUserAgents = [
        'android', 'iphone', 'ipad', 'ipod', 'blackberry',
        'windows phone', 'mobile', 'tablet'
    ];

    const userAgentIsMobile = mobileUserAgents.some(agent =>
        userAgent.includes(agent)
    );

    // For tablets in landscape mode (768px+), use desktop behavior
    if (isLandscape && screenWidth >= 768) {
        return false;
    }

    // Use mobile chat if:
    // 1. Screen width is less than 768px (definitely mobile), OR
    // 2. Portrait mode and screen width is less than 1024px, OR
    // 3. User agent indicates mobile device and not landscape with good width
    return (
        screenWidth < 768 ||
        (!isLandscape && screenWidth < 1024) ||
        (userAgentIsMobile && screenWidth < 768)
    );
};