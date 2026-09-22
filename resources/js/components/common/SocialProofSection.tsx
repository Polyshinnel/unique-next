import ImageView from 'next/image';
import {
    Badge,
    Button,
    Container,
    SimpleGrid,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import { IconExternalLink } from '@tabler/icons-react';

const socialCards = [
    {
        badge: 'ВКонтакте',
        badgeColor: 'orange',
        title: 'Наша группа в ВКонтакте',
        description: 'Мы публикуем наши отгрузки в нашей группе в ВК, чтобы сделать нашу работу максимально открытой и работать максимально прозрачно. Там найдете информацию аж с 2019 года.',
        href: 'https://vk.com/uniqset',
        action: 'Перейти во ВКонтакте',
        qrSrc: '/assets/img/qr-vk-uniqset.svg',
        qrAlt: 'QR-код для перехода в группу ЮНИК С во ВКонтакте',
    },
    {
        badge: 'Авито',
        badgeColor: 'blue',
        title: 'Наш Авито - магазин',
        description: 'Мы также представлены на Авито. Переходите, смотрите объявления и задавайте вопросы напрямую. Также именно на Авито вы найдете реальные отзывы о нашей работе. Мы на Авито с 2020 года.',
        href: 'https://www.avito.ru/brands/i182086396',
        action: 'Открыть Авито',
        qrSrc: '/assets/img/qr-avito-uniqset.svg',
        qrAlt: 'QR-код для перехода в профиль ЮНИК С на Авито',
    },
    {
        badge: 'Telegram',
        badgeColor: 'cyan',
        title: 'Канал в Телеграм',
        description: 'Почему стоит следить за каналами? Приоритет 48 часов: Станки появляются здесь сразу же после появления в онлайн-каталоге. На Авито и др. площадки они попадают только через пару дней, если их не заберут в каналах.',
        href: 'https://t.me/uniqset_catalog',
        action: 'Канал в Телеграм',
        qrSrc: '/assets/img/qr-telegram-uniqset-catalog.svg',
        qrAlt: 'QR-код для перехода в каталог ЮНИК С в Telegram',
    },
    {
        badge: 'MAX',
        badgeColor: 'dark',
        title: 'Канал в МАКС',
        description: 'Все новинки, изменения цены и условий продажи мгновенно публикуем в наших каналах в Максе и в Телеграм. Информация в оба канала транслируется практически одновременно.',
        href: 'https://max.ru/id4027139409_biz',
        action: 'Канал в МАКСe',
        qrSrc: '/assets/img/qr-max-uniqset.svg',
        qrAlt: 'QR-код для перехода в MAX ЮНИК С',
    },
] as const;

type SocialProofSectionProps = {
    className?: string;
};

export function SocialProofSection({ className = 'content-section content-section--white social-proof-section' }: SocialProofSectionProps) {
    return (
        <section className={className}>
            <Container size="xl">
                <Stack gap="xl">
                    <Stack gap={6}>
                        <Title order={2}>Где можно посмотреть нас ближе</Title>
                        <Text c="dimmed" maw={760}>
                            Переходите в наши публичные каналы, чтобы увидеть новые предложения, отзывы и реальные кейсы.
                        </Text>
                    </Stack>

                    <div className="otgruzki-follow-summary">
                        <Badge variant="filled" color="orange" size="lg">97%</Badge>
                        <Text size="lg">
                            <strong>97% клиентов довольны нашей работой</strong> и возвращаются к нам за следующими
                            сделками или рекомендуют нас коллегам.
                        </Text>
                    </div>

                    <SimpleGrid cols={{ base: 1, lg: 2 }} spacing="lg">
                        {socialCards.map((card) => (
                            <article key={card.title} className="otgruzki-follow-card">
                                <div className="otgruzki-follow-card__content">
                                    <div className="otgruzki-follow-card__body">
                                        <Badge variant="light" color={card.badgeColor}>{card.badge}</Badge>
                                        <Title order={3}>{card.title}</Title>
                                        <Text c="dimmed">{card.description}</Text>
                                        <Button
                                            component="a"
                                            href={card.href}
                                            target="_blank"
                                            rel="noreferrer"
                                            rightSection={<IconExternalLink size={17} />}
                                        >
                                            {card.action}
                                        </Button>
                                    </div>

                                    <div className="otgruzki-follow-card__qr">
                                        <div className="otgruzki-follow-card__qr-frame">
                                            <ImageView
                                                src={card.qrSrc}
                                                alt={card.qrAlt}
                                                width={220}
                                                height={220}
                                                unoptimized
                                            />
                                        </div>
                                        <Text size="sm" c="dimmed" ta="center">
                                            Сканируйте QR-код, чтобы открыть страницу сразу на телефоне.
                                        </Text>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}
