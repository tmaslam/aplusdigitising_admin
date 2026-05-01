<?php

namespace App\Http\Controllers;

use App\Support\PortalMailer;
use App\Support\PublicSitePricing;
use App\Support\SiteContext;
use App\Support\SignupOfferService;
use App\Support\CustomerPublicRateLimit;
use App\Support\EmailValidation;
use App\Support\TurnstileVerifier;
use Illuminate\Http\Request;

class CustomerSiteController extends Controller
{
    private const SERVICE_PAGES = [
        'embroidery-digitizing' => [
            'title' => 'Embroidery Digitizing',
            'image' => '/images/embroidery-digitizing-services-1.webp',
            'banner_image' => '/images/banner-embroidery-%20digitizing-%20services.webp',
            'page_heading' => 'Embroidery Digitizing Services',
            'meta_description' => 'Professional embroidery digitizing for logos, caps, jackets, flats, and production-ready files.',
            'paragraphs' => [
                'Embroidery digitizing services is the process of converting artwork or designs into digital files that can be read by computerized embroidery machines. This involves using specialized software to recreate the design in a format that the machine can understand. The digitizer analyzes the design, determines stitch types, and assigns specific instructions for the machine to follow. This includes selecting the type of stitches, defining stitch direction, assigning thread colors, and determining stitch density. Once the design is digitized, it can be transferred to the embroidery machine where the design is meticulously stitched onto fabrics or garments.',
                'Today, the popularity of various embroidery products is mostly achieved through the exclusive process of embroidery stamping. This special embroidery task is mainly done by means of modern custom embroidery digitizing software program. Mainly choose a variety of digital skills and highly creative individuals to produce special designs in digital format to further embroider a variety of different clothing and other products.',
                'In this regard, marketing is a basic area of trade, with a special focus on modern embroidery digitizing facilities to promote a variety of newly established brands, labels or businesses. For example, embroidered logos for handbags, T-shirts, headscarves, and many such related items are publicly launched to capture the deeper level of interest of a wider audience.',
                'At the same time, the exclusive embroidery digitizing services has been widely used in the field of sports. It is possible that the sports industry, companies, teams or athletes most like to get the best embroidery digitizing facilities to personally identify their personalized names on uniforms, kits, sports bags, T-shirts, and pants.',
            ],
            'gallery_columns' => 3,
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/embroidery-digitizing-services-1.webp',
                '/images/embroidery-digitizing-services-2.webp',
                '/images/embroidery-digitizing-services-3.webp',
            ],
        ],
        '3d-puff-embroidery-digitizing' => [
            'title' => '3D / Puff Embroidery Digitizing',
            'image' => '/images/3d-puff-embroidery-digitizing-services-1.webp',
            'banner_image' => '/images/banner-3d-puff-embroidery.webp',
            'page_heading' => '3D Puff Embroidery Digitizing',
            'meta_description' => '3D puff embroidery digitizing for raised cap designs and bold logo work.',
            'paragraphs' => [
                '3D puff embroidery is the best way in terms of adding creativity and artistic feel in any custom hat or sweatshirt clothing piece. It is one such type of embroidery that impacts fully added with the 3D nature. For bringing a pop-up effect in your logo designing over jackets, hats or shirts, choosing 3D Puff embroidery digitizing is the best way for you.',
                '3D puff embroidery digitizing is a specialized technique used in embroidery to create raised or three-dimensional effects on designs. It involves the process of adding an extra layer of foam or padding beneath the stitches to make them stand out and create a raised appearance. The 3D puff embroidery digitizing technique involves designing the artwork in a way that accommodates the presence of foam.',
            ],
            'content_blocks' => [
                [
                    'title' => 'Different Options of Custom 3D Puff Embroidery:',
                    'list' => [
                        'Custom Hats',
                        'Hoodies',
                        'Workwear',
                        'Sweatshirts',
                    ],
                ],
                [
                    'title' => 'What kind of applications is used for 3D Puff Embroidery?',
                    'list' => [
                        'Trade shows',
                        'Recruiting events',
                        'Industrial custom wear',
                        'Custom Hats',
                        'Promotional products',
                        'Custom logo uniforms',
                    ],
                ],
                [
                    'title' => 'When can you use 3D Puff Embroidery for Decoration Method?',
                    'paragraphs' => [
                        'Many users of custom embroidery want to know that when they can make the use of 3D puff embroidery digitizing for decoration method. If you think that this embroidery method is the easy customization method, then you are completely wrong with this concept. It is comprised of some added steps and processing method that makes it much time consuming and pricey too.',
                    ],
                    'list' => [
                        'You can use 3D embroidery if you want to add some designs of the logo to pop up the clothing piece. They hence add some dimensional depth in your designs. Being raised over the surfacing makes your company logo stand out.',
                        '3D embroidery is beneficial if you want any design to last longer. It is much durable in longer lasting results as compared to some ordinary embroidery type.',
                        'It simply brings a high-quality look in your embroidery clothing design. Hats and sweatshirts being added with the 3D puff effects somehow bring stylish and extra sophisticated impression.',
                    ],
                ],
            ],
            'gallery_columns' => 3,
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/3d-puff-embroidery-digitizing-services-1.webp',
                '/images/3d-puff-embroidery-digitizing-services-2.webp',
                '/images/3d-puff-embroidery-digitizing-services-3.webp',
            ],
        ],
        'applique-embroidery-digitizing' => [
            'title' => 'Applique Embroidery Digitizing',
            'image' => '/images/applique-embroidery-digitizing-1.webp',
            'banner_image' => '/images/banner-applique-embroidery-digitizing%20.webp',
            'page_heading' => 'Applique Embroidery Digitizing',
            'meta_description' => 'Applique embroidery digitizing with clean placement, tack-down runs, and production-friendly sequencing.',
            'paragraphs' => [
                'Applique embroidery digitizing is a technique that combines fabric layering and embroidery to create stunning designs. The process involves converting a design into a digital file, with each fabric piece represented as a separate element. The digitizer specifies stitch types, colors, and placement lines for the fabric pieces.',
                'Applique requires a number of stitches to annex the applied pieces onto the material base. The most common stitch is the straight stitch. Running stitch or straight stitch is the common type of stitching practiced while attaching applique patches on the material or fabric base.',
                'Applique embroidery digitizing services is considered as one of the most demanding embroidery styles in the fashion industry. People love to wear applique embroidery designed dresses, and the style remains popular because it adds texture, dimension, and bold layered character.',
            ],
            'gallery_columns' => 3,
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/applique-embroidery-digitizing-1.webp',
                '/images/applique-embroidery-digitizing-2.webp',
                '/images/applique-embroidery-digitizing-3.webp',
            ],
        ],
        'chain-stitch-embroidery-digitizing' => [
            'title' => 'Chain Stitch Embroidery Digitizing',
            'image' => '/images/Chain-Stitch-Embroidery-Digitizing(1).webp',
            'banner_image' => '/images/banner-chain-stich-embroidery%20.webp',
            'page_heading' => 'Chain Stitch Embroidery Digitizing',
            'meta_description' => 'Chain stitch embroidery digitizing for decorative lettering and textured embroidery styles.',
            'paragraphs' => [
                'Chain stitch embroidery digitizing is a technique used to create chain stitch designs using an embroidery machine. Chain stitch is a classic embroidery stitch that forms a looped chain-like pattern. In digitizing, the process involves converting the design into a digital file that the embroidery machine can read.',
                'People may be familiar with the stitches used in embroidery. The patterns used in embroidery are made by repeating these stitches or changing them. Stitches used in embroidery can be done in two ways. The first stitch is the hand stitch method, the other is called the stab method.',
                'The hand stitching method is used by placing a needle and embroidering the needle on the fabric. The stitches are done by placing the needle in the fabric and pushing it back to the top. Then thread the fabric through to make it have a simple stitching effect.',
            ],
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/Chain-Stitch-Embroidery-Digitizing(1).webp',
                '/images/Chain-Stitch-Embroidery-Digitizing(2).webp',
            ],
        ],
        'photo-digitizing' => [
            'title' => 'Photo Digitizing',
            'image' => '/images/Photo-Digitizing-Services-1.webp',
            'banner_image' => '/images/banner-photo%20-digitizing-services.webp',
            'page_heading' => 'Photo Digitizing Service',
            'meta_description' => 'Photo digitizing service for artwork that needs careful embroidery interpretation and simplification.',
            'paragraphs' => [
                'The key to an awesome print begins with the extraordinary artwork, so we put an incredible accentuation on the art method. We have a really brilliant staff, and with their assistance we are ready to do almost anything with art and photos.',
                'Photo digitizing is each so regularly misconstrued; we digitize a file that is then read as a sewing pattern by the machine. It helps the embroidery machine understand where to begin, what embroidery stitch to use, and how the color sequence should run. All the digitizing process usually takes 24 hours.',
                'A few times our customer has an idea but cannot bring it to life alone, because embroidery is not something that can be handled by a few clicks. We usually ask questions about the photo we are going to digitize, such as colors, elements to incorporate, and font preferences.',
                'Photo digitizing is every so often misunderstood, but the core of the work is still turning the idea into a file the embroidery machine can truly use.',
            ],
            'service_offers_title' => 'Services offered by APlus Digitizing:',
            'service_offers' => [
                'Logo Vectorization',
                'Logo Cleanup',
                'Embroidery Digitizing',
                'Photo Restoration',
                'Photo Colorization',
                'Logo Creation',
                'Color Separation',
                'Film Output',
                'Wholesale Price Available For',
            ],
            'gallery_columns' => 3,
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/Photo-Digitizing-Services-1.webp',
                '/images/Photo-Digitizing-Services-3.webp',
                '/images/Photo-Digitizing-Services-2.webp',
            ],
        ],
        'vector-art' => [
            'title' => 'Vector Art Services',
            'image' => '/images/vector-art-services-1.webp',
            'banner_image' => '/images/banner-vector-art-services%20.webp',
            'page_heading' => 'Vector Art Services',
            'meta_description' => 'Vector art service for logo redraws, print-ready artwork, and clean scalable production files.',
            'paragraphs' => [
                'Vector art is created using mathematical equations and consists of point, shapes and lines. Unlike raster images, such as JPEG or PNG files, vector art can be scaled up or down without losing quality. Vector art services usually involve using specialized software to trace or recreate images, logos, or illustrations. This process involves manually creating anchor points and curves to accurately reproduce the shapes and lines of the original art work. The resulting vector files can then be used for various purposes, such as printing, embroidery, engraving, or digital design, where high-resolution and scalability are crucial.',
                'For example: To complete your vector art work, you think your work is missing something, you put it in Photoshop and give it a small texture, trying to do it more. At that time it was no longer a vector job, you should upload it to "Digital Art to Mixed Media". Similarly, if you\'re using rasterized textures and putting them in Illustrator by applying layer styles; it\'s not vector art work.',
                'Vector art is a technique that means the art created with vector-based programs or by using vector art services. Vector art basically uses points, lines, and curves. Vector programs notice the relationship between these elements. This allows the created image to change its scale without loss of quality or pixelation. In contrast, pixels lose quality when they are raised above 100%.',
            ],
            'hide_highlights' => true,
            'gallery_images' => [
                '/images/vector-art-services-1.webp',
                '/images/vector-art-services-2.webp',
            ],
        ],
    ];

    public function home(Request $request)
    {
        if ($request->session()->has('customer_user_id')) {
            return redirect('/dashboard.php');
        }

        return redirect('/login.php');
    }

    public function workProcess(Request $request)
    {
        return view('public.work-process', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function about(Request $request)
    {
        return view('public.about', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function quality(Request $request)
    {
        return view('public.quality', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function services(Request $request)
    {
        return view('public.services', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function servicePage(Request $request, string $section)
    {
        $service = self::SERVICE_PAGES[$section] ?? null;

        if (! $service) {
            abort(404);
        }

        return view('public.service-detail', [
            'site' => $request->attributes->get('siteContext'),
            'service' => array_merge($service, ['slug' => $section]),
        ]);
    }

    public function pricing(Request $request)
    {
        /** @var SiteContext $site */
        $site = $request->attributes->get('siteContext');

        return view('public.pricing', [
            'site' => $site,
            'pricing' => PublicSitePricing::forSite($site),
        ]);
    }

    public function formats(Request $request)
    {
        return view('public.formats', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function paymentOptions(Request $request)
    {
        return redirect('/contact-us.php');
    }

    public function robots(Request $request)
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.$this->absoluteUrl($request, '/sitemap.xml'),
            '',
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(Request $request)
    {
        return response()
            ->view('public.sitemap', [
                'urls' => $this->publicSiteUrls($request),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function contact(Request $request)
    {
        return view('public.contact', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function privacyPolicy(Request $request)
    {
        return view('public.privacy-policy', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function terms(Request $request)
    {
        return view('public.terms', [
            'site' => $request->attributes->get('siteContext'),
        ]);
    }

    public function sendContact(Request $request)
    {
        /** @var SiteContext $site */
        $site = $request->attributes->get('siteContext');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', EmailValidation::rule(), 'max:190'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'website_url' => ['nullable', 'string', 'max:1'],
        ]);

        if (trim((string) ($validated['website_url'] ?? '')) !== '') {
            return back()->with('success', 'Thanks. Your message has been received.');
        }

        if (! TurnstileVerifier::verify($request, 'public-contact')) {
            return back()->withErrors(['contact' => 'Please complete the security verification and try again.'])->withInput();
        }

        if (CustomerPublicRateLimit::tooManyAttempts($request, 'contact', $site->legacyKey, strtolower(trim((string) $validated['email'])), 5, 600)) {
            return back()->withErrors(['contact' => 'Too many messages were sent from this connection. Please try again later.'])->withInput();
        }

        $recipient = (string) config('mail.admin_alert_address', $site->supportEmail);
        $subject = '['.$site->displayLabel().'] '.trim((string) $validated['subject']);
        $body = view('customer.emails.contact-message', [
            'siteContext' => $site,
            'payload' => array_merge($validated, [
                'ip_address' => (string) ($request->ip() ?? '127.0.0.1'),
            ]),
        ])->render();

        $sent = PortalMailer::sendHtml($recipient, $subject, $body);

        return $sent
            ? back()->with('success', 'Thanks. Your message has been received.')
            : back()->withErrors(['contact' => 'We could not send your message right now. Please try again or email support directly.']);
    }

    private function publicSiteUrls(Request $request): array
    {
        $urls = [
            ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['path' => '/about-us.php', 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['path' => '/our-quality.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/work-process.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/price-plan.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['path' => '/formats.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/contact-us.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/privacy-policy.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['path' => '/terms.php', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        foreach (array_keys(self::SERVICE_PAGES) as $slug) {
            $urls[] = [
                'path' => '/'.$slug.'.php',
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return array_map(function (array $url) use ($request): array {
            return [
                'loc' => $this->absoluteUrl($request, $url['path']),
                'changefreq' => $url['changefreq'],
                'priority' => $url['priority'],
            ];
        }, $urls);
    }

    private function absoluteUrl(Request $request, string $path): string
    {
        $base = rtrim($request->getSchemeAndHttpHost(), '/');

        if ($path === '' || $path === '/') {
            return $base.'/';
        }

        return $base.'/'.ltrim($path, '/');
    }
}
