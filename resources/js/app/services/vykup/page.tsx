import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { phoneHref, siteContacts } from '@/lib/site-content';
import {
    Button,
    Container,
    Group,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
    Textarea,
    Title,
} from '@mantine/core';
import {
    IconCalculator,
    IconChecklist,
    IconCrane,
    IconMapPin,
    IconPhone,
    IconRosetteDiscountCheck,
    IconTruck,
    IconUsersGroup,
} from '@tabler/icons-react';

export const metadata: Metadata = {
    title: 'Выкуп оборудования | ЮНИК С',
    description: 'Выкуп и реализация промышленного оборудования по рыночным ценам с сопровождением сделки по всей России.',
};

const equipmentList = [
    'Металлорежущие и металлообрабатывающие станки',
    'Обрабатывающие центры и станки с ЧПУ',
    'Деревообрабатывающие станки',
    'Станки для обработки листового металла',
    'Гидравлические и электро-механические пресса',
    'Высоко тоннажное прессовое оборудование',
    'Станки шлифовальной группы',
    'Оборудование лазерной, плазменной и прочей резки',
    'Электроэрозионные станки',
    'Погрузочное оборудование, краны, кран-балки',
    'Спецтехнику: трактора, погрузчики, авто-краны и экскаваторы',
    'Производственные линии или промышленные системы',
    'Промышленные роботы и автоматизированные ячейки',
    'И прочее, прочее',
] as const;

const regionList = [
    'Калуга и Калужская область',
    'Смоленск и Смоленская область',
    'Тула и Тульская область',
    'Йошкар-Ола и Марий Эл',
    'Тольятти и Самарская область',
    'Нижний Новгород и Нижегородская область',
    'Киров и Кировская область',
    'Волгоград и Волгоградская область',
    'Уфа и республика Башкортостан',
    'Брянск, Орел, Рославль',
    'Ижевск и республика Удмуртия',
    'Ульяновск и Ульяновская область',
    'Челябинск и Челябинская область',
] as const;

const partnerCards = [
    {
        icon: IconUsersGroup,
        title: 'Умеем продавать',
        text: 'Мы знаем текущие потребности рынка, а также у нас большая клиентская база по всей России.',
    },
    {
        icon: IconChecklist,
        title: 'Отсечем «пустышки»',
        text: 'Избавим вас от необходимости общаться с «любопытными» и желающими получить КП.',
    },
    {
        icon: IconRosetteDiscountCheck,
        title: 'Работаем профессионально',
        text: 'Занимаемся реализацией оборудования на профессиональной основе. Мы вкладываемся в маркетинг и работаем на всех актуальных площадках.',
    },
    {
        icon: IconCrane,
        title: 'Решим сопутствующие вопросы',
        text: 'У нас большой опыт по организации демонтажных работ, есть подрядчики, привлечем спецтехнику: манипуляторы, краны, платформы, тралы.',
    },
    {
        icon: IconCalculator,
        title: 'Работаем с вашей ценой',
        text: 'Отталкиваемся от ваших ценовых ожиданий. Состыкуем любую ситуацию между вами и клиентом: безнал с НДС, без НДС и другие форматы сделки.',
    },
    {
        icon: IconTruck,
        title: 'Работаем по всей РФ',
        text: 'Мы умеем отвечать на вопросы клиентов дистанционно. Через партнеров и АТИ организуем любой транспорт: негабарит, сборные грузы, попутные машины или ТК.',
    },
] as const;

function HeroSection() {
    return (
        <section className="page-hero buyout-hero">
            <Container size="xl">
                <div className="catalog-breadcrumbs">
                    <Link href="/">Главная</Link>
                    <span>/</span>
                    <Link href="/services">Услуги</Link>
                    <span>/</span>
                    <span>Выкуп оборудования</span>
                </div>

                <Stack gap="lg" maw={760}>
                    <Title order={1}>Выкуп оборудования</Title>
                    <Text size="lg">
                        ООО “Юник С” – торгующая организация, мы профессионально занимаемся выкупом, а также берем на
                        реализацию любое промышленное оборудование. Работаем по рыночным ценам, отталкиваясь от Ваших
                        ожиданий.
                    </Text>
                    <Group gap="md">
                        <Button
                            component="a"
                            href={phoneHref(siteContacts.phone)}
                            size="lg"
                            leftSection={<IconPhone size={18} />}
                        >
                            Позвонить
                        </Button>
                        <Button component="a" href="#buyout-form" size="lg" variant="white" color="dark">
                            Оставить заявку
                        </Button>
                    </Group>
                </Stack>
            </Container>
        </section>
    );
}

function ListSection({
    title,
    items,
}: {
    title: string;
    items: readonly string[];
}) {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="xl">
                    <Title order={2} maw={980}>
                        {title}
                    </Title>
                    <SimpleGrid cols={{ base: 1, md: 2 }} spacing="md">
                        {items.map((item) => (
                            <div key={item} className="buyout-list-item">
                                <span className="buyout-list-item__dot" />
                                <Text>{item}</Text>
                            </div>
                        ))}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function RegionsSection() {
    return (
        <section className="content-section buyout-regions-section">
            <Container size="xl">
                <div className="buyout-regions-layout">
                    <div className="buyout-regions-card">
                        <Stack gap="xl">
                            <Title order={2}>
                                Мы проработаем вопрос реализации ЛЮБОГО промышленного оборудования, находящегося в
                                следующих регионах:
                            </Title>

                            <SimpleGrid cols={1} spacing="md">
                                {regionList.map((item) => (
                                    <div key={item} className="buyout-list-item buyout-list-item--compact">
                                        <span className="buyout-list-item__dot" />
                                        <Text>{item}</Text>
                                    </div>
                                ))}
                            </SimpleGrid>

                            <div className="buyout-note">
                                <div className="buyout-note__row">
                                    <IconMapPin size={20} />
                                    <Text>
                                        Мы постоянно расширяемся, позвоните нам: {siteContacts.phone}, возможно мы уже
                                        работаем в вашем регионе.
                                    </Text>
                                </div>
                                <Text>
                                    Мы работаем по Агентской схеме, чтобы получить оценку оборудования заполните форму
                                    справа.
                                </Text>
                            </div>
                        </Stack>
                    </div>

                    <aside id="buyout-form" className="buyout-form-card">
                        <Stack gap="md">
                            <Text className="contact-card__eyebrow">Заявка на оценку</Text>
                            <Title order={3}>Оставьте данные по оборудованию</Title>
                            <Text c="dimmed">
                                Перезвоним, уточним детали и предложим удобный формат: выкуп или реализация по
                                агентской схеме.
                            </Text>
                        </Stack>

                        <Stack gap="sm" mt="xl">
                            <TextInput label="Ваше имя" placeholder="Как к вам обращаться" />
                            <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" />
                            <TextInput label="Регион" placeholder="Город или область" />
                            <Textarea
                                label="Что хотите реализовать"
                                placeholder="Кратко опишите оборудование, количество, состояние"
                                minRows={5}
                            />
                        </Stack>

                        <Button fullWidth size="lg" mt="xl">
                            Отправить заявку
                        </Button>
                        <Text size="sm" c="dimmed" className="buyout-form-card__hint">
                            Нажимая кнопку, вы соглашаетесь на обработку персональных данных.
                        </Text>
                    </aside>
                </div>
            </Container>
        </section>
    );
}

function PartnersSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="xl">
                    <Title order={2}>ООО «Юник С» – надежный партнер на рынке б.у. оборудования</Title>
                    <SimpleGrid cols={{ base: 1, md: 2, xl: 3 }} spacing="lg">
                        {partnerCards.map((item) => {
                            const Icon = item.icon;

                            return (
                                <article key={item.title} className="buyout-feature-card">
                                    <span className="buyout-feature-card__icon">
                                        <Icon size={24} />
                                    </span>
                                    <Title order={3}>{item.title}</Title>
                                    <Text c="dimmed">{item.text}</Text>
                                </article>
                            );
                        })}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function SummarySection() {
    return (
        <section className="content-section buyout-summary-section">
            <Container size="xl">
                <div className="buyout-summary-card">
                    <Stack gap="lg" maw={980}>
                        <Text size="lg">
                            Мы торгующая компания, профессионально работающая в сфере купли-продажи б.у. промышленного
                            оборудования.
                        </Text>
                        <Text size="lg">
                            У нас большая база постоянных клиентов, а также большой входящий поток – каждый день мы
                            получаем порядка 25 новых запросов. Часто выкупаем оборудование в течение нескольких
                            следующих дней после осмотра. Работаем с вашей ценой. При необходимости можем провести
                            оценку стоимости оборудования. Предложим удобную форму работы: выкуп или сработаем по
                            агентской схеме.
                        </Text>
                        <Text size="lg">
                            Занимаясь каждый день этой работой мы давно набили руку в решении сопутствующих вопросов
                            таких как поиск транспорта, организация демонтажа или погрузочно-разгрузочных работ.
                        </Text>
                        <Text size="lg">
                            Мы знаем как устроен и работает этот рынок, мы вкладываемся в маркетинг.
                        </Text>
                        <Text size="lg">
                            Один из самых главных принципов нашей работы – это прозрачность, наши партнеры нам
                            доверяют.
                        </Text>

                        <Group mt="md">
                            <Button
                                component="a"
                                href={phoneHref(siteContacts.phone)}
                                size="lg"
                                leftSection={<IconPhone size={18} />}
                            >
                                Заказать обратный звонок
                            </Button>
                        </Group>
                    </Stack>
                </div>
            </Container>
        </section>
    );
}

export default function EquipmentBuyoutPage() {
    return (
        <>
            <Header />
            <main>
                <HeroSection />
                <ListSection title="Берем на реализацию следующее оборудование:" items={equipmentList} />
                <RegionsSection />
                <PartnersSection />
                <SummarySection />
            </main>
            <Footer />
        </>
    );
}
