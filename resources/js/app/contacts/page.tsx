import type { Metadata } from 'next';
import { ContactsPageView } from '@/components/contacts/ContactsPageView';

export const metadata: Metadata = {
    title: 'Контакты | ЮНИК С',
    description: 'Контакты компании ЮНИК С: телефон, email, адрес офиса в Калуге, режим работы и карта проезда.',
};

export default function ContactsPage() {
    return <ContactsPageView />;
}
