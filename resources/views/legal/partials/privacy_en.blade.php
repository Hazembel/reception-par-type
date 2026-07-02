<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Data Controller</h2>
    <p>
        <strong>{{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}</strong><br>
        {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Switzerland<br>
        Email: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Data Collected</h2>
    <p>We collect the following data in connection with the use of our services:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Account data:</strong> email address, encrypted password (bcrypt)</li>
        <li><strong>Payment data:</strong> amount, currency, PayPal order ID (we never store your card details)</li>
        <li><strong>Billing data:</strong> name, address (for invoices)</li>
        <li><strong>Usage data:</strong> searches performed, sheets viewed, date and time of access</li>
        <li><strong>Technical data:</strong> IP address, browser type, operating system (server logs)</li>
        <li><strong>Session cookies:</strong> required for authentication</li>
        <li><strong>Affiliate tracking cookie:</strong> affiliate code, retained for 30 days</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Purposes and Legal Bases</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse mt-2">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Purpose</th>
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Legal basis (GDPR)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Service provision</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — contract performance</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Payment processing and invoicing</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — contract performance</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Legal obligations (invoice retention)</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(c) — legal obligation</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Security and fraud prevention</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(f) — legitimate interests</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Affiliate programme management</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — contract performance</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Retention Periods</h2>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Account data:</strong> duration of contractual relationship + 3 years after termination</li>
        <li><strong>Invoices and accounting data:</strong> 10 years (legal obligation, Art. 958f CO)</li>
        <li><strong>Server access logs:</strong> 90 days</li>
        <li><strong>Session cookies:</strong> duration of session or until logout</li>
        <li><strong>Affiliate cookies:</strong> 30 days</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Data Recipients</h2>
    <p>Your data may be transmitted to the following processors:</p>
    <ul class="mt-2 space-y-2 list-disc list-inside">
        <li><strong>PayPal</strong> — payment processing. <a href="https://www.paypal.com/en/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">PayPal Privacy Policy</a></li>
        <li><strong>Hosting provider</strong> — server hosting (Europe)</li>
        <li><strong>OFROU/ASTRA</strong> — source of vehicle technical data (no user data transferred)</li>
    </ul>
    <p class="mt-2">We do not sell or rent your personal data to third parties.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">International Transfers</h2>
    <p>Our servers are located in Europe. Payments are processed by PayPal (established in Luxembourg, EU). Apart from PayPal, your data is not transferred outside Switzerland or the European Economic Area.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Cookies</h2>
    <p>We use only strictly necessary cookies:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Session cookie (XSRF-TOKEN, session)</strong>: authentication and CSRF protection</li>
        <li><strong>Affiliate cookie (rpt_ref)</strong>: referral tracking, 30 days</li>
    </ul>
    <p class="mt-2">We do not use advertising tracking or behavioural analytics cookies.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Your Rights</h2>
    <p>Under GDPR (EU 2016/679) and the Swiss Federal Act on Data Protection (FADP), you have the following rights:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Right of access:</strong> obtain a copy of your personal data</li>
        <li><strong>Right to rectification:</strong> correct inaccurate data</li>
        <li><strong>Right to erasure:</strong> request deletion of your data</li>
        <li><strong>Right to restriction:</strong> restrict processing in certain cases</li>
        <li><strong>Right to portability:</strong> receive your data in a structured format</li>
        <li><strong>Right to object:</strong> object to processing based on legitimate interests</li>
    </ul>
    <p class="mt-3">
        To exercise your rights: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a><br>
        We respond within 30 days. In case of dispute, you may lodge a complaint with the <a href="https://www.edoeb.admin.ch" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Federal Data Protection and Information Commissioner (FDPIC)</a>.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Security</h2>
    <p>We apply appropriate technical and organisational measures: HTTPS encryption (TLS), password hashing (bcrypt), CSRF protection, role-based access control. Please report any anomaly to <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Changes</h2>
    <p>We reserve the right to modify this policy at any time. In the event of a material change, registered users will be notified by email. The current version is always available at this address.</p>
</section>
