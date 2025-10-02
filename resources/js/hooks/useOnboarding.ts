import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface UseOnboardingReturn {
    showOnboarding: boolean;
    openOnboarding: () => void;
    closeOnboarding: () => void;
}

export function useOnboarding(): UseOnboardingReturn {
    const { auth } = usePage<{ auth: { user?: { onboarding_completed?: boolean } } }>().props;
    const [showOnboarding, setShowOnboarding] = useState(false);

    useEffect(() => {
        // Show onboarding for first-time users
        if (auth.user && !auth.user.onboarding_completed) {
            // Small delay to let the page load
            const timeout = setTimeout(() => {
                setShowOnboarding(true);
            }, 1000);

            return () => clearTimeout(timeout);
        }
    }, [auth.user]);

    const openOnboarding = () => setShowOnboarding(true);
    const closeOnboarding = () => setShowOnboarding(false);

    return {
        showOnboarding,
        openOnboarding,
        closeOnboarding,
    };
}
