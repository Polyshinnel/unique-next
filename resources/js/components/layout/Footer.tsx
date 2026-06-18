import Link from 'next/link';
import {
    Anchor,
    Button,
    Container,
    Divider,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
} from '@mantine/core';
import {
    IconBrandTelegram,
    IconBrandVk,
    IconMail,
    IconMapPin,
    IconMessageCircle,
    IconPhone,
} from '@tabler/icons-react';
import { demoServices, emailHref, navItems, phoneHref, siteContacts } from '@/lib/site-content';

function SocialButton({ href, icon: Icon, label }: { href: string; icon: typeof IconBrandVk; label: string }) {
    return (
        <Anchor href={href} className={`social-button ${href === '#' ? 'social-button--disabled' : ''}`}>
            <span className="social-button__icon">
                <Icon size={18} />
            </span>
            <span className="social-button__label">{label}</span>
        </Anchor>
    );
}

export function Footer() {
    const year = new Date().getFullYear();

    return (
        <footer className="site-footer">
            <Container size="xl">
                <SimpleGrid cols={{ base: 1, sm: 2, lg: 5 }} spacing="xl">
                    <Stack gap="md">
                        <Image src="/assets/img/unique-logo.png" alt="ЮНИК С" className="footer-logo" />
                        <Text c="white" fw={700}>ООО “Юник С”</Text>
                        <Text c="gray.4" size="sm">ИНН: 4027139409</Text>
                        <Text c="gray.4" size="sm">ОГРН: 1194027002861</Text>
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Разделы</Text>
                        {navItems.slice(1).map((item) => (
                            <Anchor key={item.href} component={Link} href={item.href} className="footer-link">
                                {item.label}
                            </Anchor>
                        ))}
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Документы</Text>
                        <Anchor href="#" className="footer-link">Охрана труда</Anchor>
                        <Anchor href="#" className="footer-link">Политика конфиденциальности</Anchor>
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Услуги</Text>
                        {demoServices.map((service) => (
                            <Anchor
                                key={service.id}
                                component={Link}
                                href={`/services/${service.slug}`}
                                className="footer-link"
                            >
                                {service.title}
                            </Anchor>
                        ))}
                    </Stack>

                    <Stack gap="md">
                        <Text c="white" fw={700}>Контакты</Text>
                        <Anchor href={phoneHref(siteContacts.phone)} className="footer-contact">
                            <span className="footer-contact__row">
                                <IconPhone size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.phone}</span>
                            </span>
                        </Anchor>
                        <Anchor href={emailHref(siteContacts.email)} className="footer-contact">
                            <span className="footer-contact__row">
                                <IconMail size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.email}</span>
                            </span>
                        </Anchor>
                        <span className="footer-contact">
                            <span className="footer-contact__row">
                                <IconMapPin size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.address}</span>
                            </span>
                        </span>
                        <Group gap="xs">
                            <SocialButton href={siteContacts.socialLinks.max} icon={IconMessageCircle} label="Max" />
                            <SocialButton href={siteContacts.socialLinks.vk} icon={IconBrandVk} label="VK" />
                            <SocialButton href={siteContacts.socialLinks.telegram} icon={IconBrandTelegram} label="Telegram" />
                        </Group>
                    </Stack>
                </SimpleGrid>

                <Divider color="rgba(255,255,255,.14)" my="xl" />

                <Group justify="space-between" gap="md" className="footer-bottom">
                    <Stack gap={4} className="footer-legal">
                        <Text c="gray.5" size="sm">© “ЮНИК С” 2019-{year}</Text>
                        <Text c="gray.5" size="sm" className="footer-legal__text">
                            Вся представленная на сайте информация носит информационный характер и не является публичной
                            офертой, определяемой положениями ст. 437 (2) ГК РФ. Опубликованная на данном сайте
                            информация может быть изменена в любое время без предварительного уведомления.
                        </Text>
                    </Stack>
                    <Stack gap={8} align="flex-end">
                        <Group gap="xs" className="footer-request">
                            <TextInput placeholder="Ваш телефон" aria-label="Телефон для обратного звонка" />
                            <Button>Позвоните мне</Button>
                        </Group>
                        <Text size="sm" c="gray.5" className="footer-consent">
                            Нажимая кнопку соглашаюсь с{' '}
                            <Anchor href="#" className="footer-consent__link">
                                Политикой конфиденциальности
                            </Anchor>
                        </Text>
                    </Stack>
                </Group>
            </Container>
        </footer>
    );
}
