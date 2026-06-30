import type { Metadata } from 'next';
import { getPageSeo, toMetadata } from '@/lib/seo';
import { getSiteContacts } from '@/lib/site-contacts';
import { ContactsPageView } from '@/components/contacts/ContactsPageView';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('contacts');

    return toMetadata(seo, {
        title: 'Контакты | ЮНИК С',
        description: 'Контакты компании ЮНИК С: телефон, email, адрес офиса в Калуге, режим работы и карта проезда.',
    });
}

export default async function ContactsPage() {
    const contacts = await getSiteContacts();

    return <ContactsPageView contacts={contacts} />;
}
