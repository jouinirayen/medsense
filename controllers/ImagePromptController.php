<?php

class ImagePromptController
{

    private $styles = [
        "Photorealistic",
        "Minimalist",
        "Modern Medical",
        "Clean and Sterile",
        "Warm and Welcoming",
        "High Tech",
        "Soft Focus"
    ];

    private $lighting = [
        "Natural daylight",
        "Soft studio lighting",
        "Cinematic lighting",
        "Bright and airy",
        "Warm ambient light"
    ];

    private $vibes = [
        "Professional",
        "Reassuring",
        "Calm",
        "Trustworthy",
        "Advanced",
        "Caring"
    ];

    private $colors = [
        "Blue and white color palette",
        "Pastel tones",
        "Clean white background",
        "Soft green accents",
        "Neutral tones"
    ];

    public function generatePrompt($serviceName)
    {
        // Strict Professional Medical Prompt
        // Removing randomness to ensure 100% relevance
        return sprintf(
            "Medical photography of %s, modern clinical hospital environment, professional lighting, photorealistic, 4k resolution, high quality, sharp details, reassuring atmosphere.",
            $serviceName
        );
    }
}
