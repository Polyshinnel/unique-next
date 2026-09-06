import type { Metadata } from 'next';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { getSiteContacts } from '@/lib/site-contacts';
import { ContactsPageView } from '@/components/contacts/ContactsPageView';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('contacts');

    return toMetadata(seo, {
        title: 'Контакты | ЮНИК С',
        description: 'Контакты компании ЮНИК С: телефон, email, адрес офиса в Калуге, режим работы и карта проезда.',
        alternates: {
            canonical: canonicalUrl('/contacts'),
        },
    });
}

export default async function ContactsPage() {
    const contacts = await getSiteContacts();

    return <><PageStructuredData seoKey="contacts" path="/contacts" pageType="ContactPage" fallbackTitle="Контакты | ЮНИК С" fallbackDescription="Контакты компании ЮНИК С: телефон, email, адрес офиса в Калуге, режим работы и карта проезда." /><ContactsPageView contacts={contacts} /></>;
}
